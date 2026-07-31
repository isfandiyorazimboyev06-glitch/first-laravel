<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostController;

use App\Http\Controllers\ExportStaffExcelController;

use App\Jobs\ExportStaffExcelJob;

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::resource("posts", PostController::class);

Route::get('/greeting', function () {
    return "Hello, Isfandiyor. What's up?";
});

Route::get('/export-staff', function (Request $request) {
    // 1. Fetch chat ID from query or fallback to env default
    $chatId = $request->input('chat_id') ?? env('CHAT_ID');

    if (!$chatId) {
        return "Error: Please provide ?chat_id= or set CHAT_ID in .env";
    }

    // 2. Dispatch job asynchronously
    ExportStaffExcelJob::dispatch($chatId, $request->input('search'));

    // 3. Return immediate response string to browser
    return "Excel export job queued! Check your Telegram chat in a moment.";
});
