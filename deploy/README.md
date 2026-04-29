# AI Inbox To-Do — Deployment

Target: `todo.chargeragency.com.ua` on `159.69.193.156` (Hetzner / Debian / Ubuntu).
Path layout: `/var/www/todo/{releases,current,shared}` (Capistrano-style).

Deploy SSH key (already on the server): `~/.ssh/deploy_charger`
(ED25519, comment `github-actions-deploy`).

---

## 1. One-time server setup

### Packages
```bash
sudo apt update && sudo apt install -y \
  nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-bcmath php8.2-zip php8.2-intl \
  php8.2-redis composer mysql-server supervisor git certbot python3-certbot-nginx
```

### Node 20 (for `npm run build`)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash
sudo apt install -y nodejs
```

### MySQL
```bash
sudo mysql -e "CREATE DATABASE ai_inbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'ai_inbox'@'127.0.0.1' IDENTIFIED BY 'STRONG_PASSWORD';"
sudo mysql -e "GRANT ALL ON ai_inbox.* TO 'ai_inbox'@'127.0.0.1'; FLUSH PRIVILEGES;"
```

### Filesystem
```bash
sudo mkdir -p /var/www/todo/{releases,shared} /var/log/ai-inbox
sudo chown -R www-data:www-data /var/www/todo /var/log/ai-inbox
sudo -u www-data mkdir -p /var/www/todo/shared/storage/{app,framework/{cache,data,sessions,testing,views},logs}
```

### nginx + TLS
```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/todo.chargeragency.com.ua
sudo ln -sf /etc/nginx/sites-available/todo.chargeragency.com.ua /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d todo.chargeragency.com.ua --redirect -m you@example.com --agree-tos -n
```

### Supervisor (queue worker)
```bash
sudo cp deploy/supervisor-worker.conf /etc/supervisor/conf.d/ai-inbox-worker.conf
sudo supervisorctl reread && sudo supervisorctl update
```

### Cron (scheduler)
```bash
sudo crontab -u www-data -e   # paste contents of deploy/cron.txt
```

---

## 2. First deployment (manual)

```bash
RELEASE=/var/www/todo/releases/$(date +%Y%m%d%H%M%S)
sudo -u www-data git clone git@github.com:OWNER/REPO.git $RELEASE
cd $RELEASE
sudo -u www-data cp /var/www/todo/shared/.env .env     # provision .env first (see step 3)
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
sudo -u www-data npm ci && sudo -u www-data npm run build
sudo -u www-data rm -rf storage && sudo -u www-data ln -s /var/www/todo/shared/storage storage
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo ln -sfn $RELEASE /var/www/todo/current
sudo systemctl reload php8.2-fpm
sudo supervisorctl restart ai-inbox-worker:*
```

---

## 3. Provisioning `.env`

```bash
sudo -u www-data cp /var/www/todo/current/.env.example /var/www/todo/shared/.env
sudo -u www-data php /var/www/todo/current/artisan key:generate --env-file=/var/www/todo/shared/.env
sudo -u www-data nano /var/www/todo/shared/.env
```

Fill in:
- `DB_PASSWORD` (MySQL user above)
- `ANTHROPIC_API_KEY` (rotate the dev key before going live!)
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` (Google Cloud → OAuth client → Web app, redirect `https://todo.chargeragency.com.ua/auth/google/callback`)
- Source provider tokens you intend to enable (Slack/monday/Wrike/Telegram bot token).

---

## 4. CI/CD (GitHub Actions)

A starter workflow is in `.github/workflows/deploy.yml`. Add repo secrets:
- `DEPLOY_SSH_KEY` — private contents of `~/.ssh/deploy_charger`
- `DEPLOY_HOST` — `159.69.193.156`
- `DEPLOY_USER` — `deploy` (or whichever user owns the path)

The workflow checks out the repo, copies it to the server, runs composer + npm + migrate, and atomically swaps the `current` symlink.

---

## 5. Telegram webhook

After deploy, point Telegram updates at:
`POST https://todo.chargeragency.com.ua/webhooks/telegram/{token}`

Where `{token}` matches `settings.webhook_token` on the source account.
Set via:
```bash
curl -s "https://api.telegram.org/bot$TOKEN/setWebhook?url=https://todo.chargeragency.com.ua/webhooks/telegram/$WEBHOOK_TOKEN"
```

---

## 6. Smoke tests

```bash
curl -I https://todo.chargeragency.com.ua/login        # 200
curl -I https://todo.chargeragency.com.ua/dashboard    # 302 → /login
sudo -u www-data php /var/www/todo/current/artisan inbox:sync --help
sudo supervisorctl status                              # ai-inbox-worker_00 RUNNING
sudo journalctl -u php8.2-fpm -n 20
```
