<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before failing.
     */
    public $tries = 3;

    /**
     * Timeout in seconds.
     */
    public $timeout = 60;

    protected array $userIds;
    protected string $title;
    protected string $body;
    protected array $data;

    /**
     * Create a new push notification job instance.
     *
     * @param array  $userIds Recipients user IDs
     * @param string $title   Notification title
     * @param string $body    Notification body text
     * @param array  $data    Key-value extra payload (strings only)
     */
    public function __construct(array $userIds, string $title, string $body, array $data = [])
    {
        $this->onQueue('notifications');
        $this->userIds = $userIds;
        $this->title   = $title;
        $this->body    = $body;
        $this->data    = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcm): void
    {
        Log::info('SendFcmPushJob: dispatching', [
            'recipients_count' => count($this->userIds),
            'title'            => $this->title,
        ]);

        $result = $fcm->sendToUsers($this->userIds, $this->title, $this->body, $this->data);

        Log::info('SendFcmPushJob: completed', [
            'sent'   => $result['sent'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'pruned' => $result['pruned'] ?? 0,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendFcmPushJob: permanently failed', [
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
