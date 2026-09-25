<?php

namespace App\Jobs;

use App\PurchaseOrder;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Timeout in seconds before the job is considered failed.
     */
    public $timeout = 60;

    protected string $action;
    protected array $params;

    /**
     * Create a new job instance.
     *
     * @param string $action Method name or action type ('po_created', 'po_forwarded', 'po_approved', 'po_rejected', 'attendance_approval', 'sendMessage')
     * @param array  $params Key-value arguments needed for that action
     */
    public function __construct(string $action, array $params = [])
    {
        $this->onQueue('whatsapp');
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsApp): void
    {
        Log::info('SendWhatsAppJob: processing', [
            'action' => $this->action,
            'params' => array_keys($this->params),
        ]);

        try {
            switch ($this->action) {
                case 'po_created':
                    $po = PurchaseOrder::find($this->params['po_id'] ?? null);
                    if ($po) {
                        $whatsApp->sendPORequestCreatedNotification($po);
                    }
                    break;

                case 'po_forwarded':
                    $po = PurchaseOrder::find($this->params['po_id'] ?? null);
                    if ($po) {
                        $whatsApp->sendPOForwardedNotification($po, $this->params['urgency'] ?? 'normal');
                    }
                    break;

                case 'po_approved':
                    $po = PurchaseOrder::find($this->params['po_id'] ?? null);
                    if ($po) {
                        $whatsApp->sendPOAdminApprovalNotification($po);
                    }
                    break;

                case 'po_rejected':
                    $po = PurchaseOrder::find($this->params['po_id'] ?? null);
                    if ($po) {
                        $whatsApp->sendPOAdminRejectionNotification($po, $this->params['reason'] ?? '');
                    }
                    break;

                case 'attendance_approval':
                    if (!empty($this->params['mobile']) && !empty($this->params['name'])) {
                        $whatsApp->sendAttendanceApprovalNotification($this->params['mobile'], $this->params['name']);
                    }
                    break;

                case 'sendMessage':
                default:
                    if (!empty($this->params['mobile']) && !empty($this->params['title'])) {
                        $whatsApp->sendMessage(
                            $this->params['mobile'],
                            $this->params['title'],
                            $this->params['content'] ?? '',
                            $this->params['issued_by'] ?? 'System',
                            $this->params['issued_at'] ?? now()->toDateTimeString()
                        );
                    }
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('SendWhatsAppJob execution error', [
                'action' => $this->action,
                'error'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppJob: permanently failed', [
            'action' => $this->action,
            'error'  => $exception->getMessage(),
        ]);
    }
}
