<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysisLog;
use App\Models\SyncLog;
use Inertia\Inertia;
use Inertia\Response;

class LogsController extends Controller
{
    public function ai(): Response
    {
        return Inertia::render('Logs/Ai', [
            'logs' => AiAnalysisLog::latest()->paginate(50),
        ]);
    }

    public function sync(): Response
    {
        return Inertia::render('Logs/Sync', [
            'logs' => SyncLog::with('sourceAccount')->latest()->paginate(50),
        ]);
    }
}
