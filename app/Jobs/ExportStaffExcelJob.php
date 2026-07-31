<?php

namespace App\Jobs;

use App\Models\Staff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSXGen;
use Throwable;

class ExportStaffExcelJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 3;
    public int $backoff = 10; // seconds before retrying

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $chatID,
        public ?string $search = null
    ) {
        // Ensure chatID is a string to avoid type issues
        $this->onQueue('excel-exports');
    }

    /**
     * Prevent overlapping of jobs for the same chat ID.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->chatID))
                ->dontRelease()
                ->expireAfter(300)
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        #ini_set('memory_limit', '512M'); // Increase memory limit for large exports

        $filePath = null;

        try {
            // 1. Define table headers inside a 2D array
            $rows = [
                ['ID', 'F.I.O', 'Phone Number','Created At','Updated At','Organization ID','Active', 'Status'],
            ];

            // 2. Query DB in chunks using lazy() to keep RAM footprint minimal
            Staff::query()
                ->select(['id', 'fio', 'telephone_number', 'created_at', 'updated_at', 'organization_id', 'active', 'status'])
                ->when($this->search, function ($query) {
                    $query->where('fio', 'ilike', '%' . $this->search . '%')
                          ->orWhere('telephone_number', 'like', '%' . $this->search . '%');
                })
                ->orderBy('id')
                ->lazy(1000)
                ->each(function (Staff $staff) use (&$rows) {
                    $rows[] = [
                        $staff->id,
                        $staff->fio,
                        $staff->telephone_number ?? '-',
                        $staff->created_at?->toDateTimeString() ?? '---',
                        $staff->updated_at?->toDateTimeString() ?? '---',
                        $staff->organization_id ?? '-',
                        $staff->active ? 'Yes' : 'No',
                        $staff->status ?? '-',
                    ];
                });

            // 3. Generate XLSX file path in local storage
            $fileName = 'Staff_Report'.'.xlsx';
            $filePath = storage_path('app/public/' . $fileName);

            // 4. Generate the Excel file via SimpleXLSXGen
            SimpleXLSXGen::fromArray($rows)->saveAs($filePath);

            // 5. Send file to Telegram via Bot API
            $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));

            $response = Http::withoutVerifying()
                ->attach(
                    'document',
                    file_get_contents($filePath),
                    #fopen($filePath,'r'),
                    $fileName
                )->post("https://api.telegram.org/bot{$botToken}/sendDocument", [
                    'chat_id' => $this->chatID,
                    'caption' => "📊 Staff List Export\nTotal rows: " . (count($rows) - 1),
                ]);

            if ($response->failed()) {
                Log::error("Telegram export upload failed: " . $response->body());
            }

        } catch (Throwable $e) {
            Log::error("Staff Export Job Error: " . $e->getMessage(), ['exception' => $e]);

            // Send an alert to user on failure
            $this->notifyUserOfError();
        } finally {
            // 6. Clean up local file to free disk space
            if ($filePath && File::exists($filePath)) {
                File::delete($filePath);
            }
        }
    }

    private function notifyUserOfError(): void
        {
            $botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));

            Http::withoutVerifying()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $this->chatID,
                'text' => '❌ Failed to generate the Excel report. Please try again later.',
            ]);
        }
}
