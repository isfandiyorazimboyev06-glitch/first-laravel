<?php

namespace App\Http\Controllers;

use App\Jobs\ExportStaffExcelJob;
use Illuminate\Http\Request;

class ExportStaffExcelController extends Controller
{
    public function __invoke(Request $request)
    {
        // Define or grab your Telegram Chat ID (e.g. 524787215 or from request)
        $chatID = $request->input('chat_id', '524787215');
        $search = $request->input('search');
        ExportStaffExcelJob::dispatch($chatID, $search)->onQueue('excel-exports');

        return response()->json([
            'status' => 'success',
            'message' => 'Excel export job queued! Check your Telegram chat in a moment.'
        ]);
    }
}
