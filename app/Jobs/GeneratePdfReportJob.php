<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Lpoout;
use App\Models\StaffPayroll;
use App\Services\InvoicePdfService;
use App\Services\LpoPdfService;
use App\Services\StaffPayrollPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GeneratePdfReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 180;

    protected string $reportType;
    protected int $entityId;
    protected array $options;

    /**
     * Create a new PDF report job.
     *
     * @param string $reportType 'lpo', 'invoice', 'payroll'
     * @param int    $entityId   ID of the model
     * @param array  $options    Optional configuration (letterheadType, recipientEmail, sendMail, etc.)
     */
    public function __construct(string $reportType, int $entityId, array $options = [])
    {
        $this->onQueue('reports');
        $this->reportType = $reportType;
        $this->entityId   = $entityId;
        $this->options    = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GeneratePdfReportJob: starting generation', [
            'type'      => $this->reportType,
            'entity_id' => $this->entityId,
        ]);

        $generatedPdfPath = null;

        try {
            switch ($this->reportType) {
                case 'lpo':
                    $lpoout = Lpoout::find($this->entityId);
                    if ($lpoout) {
                        $pdfService = new LpoPdfService();
                        $generatedPdfPath = $pdfService->generateMergedPdf($lpoout);
                        Log::info("GeneratePdfReportJob: LPO PDF generated at {$generatedPdfPath}");

                        // Optional async email dispatch
                        if (!empty($this->options['send_email']) && !empty($this->options['email_to'])) {
                            Mail::to($this->options['email_to'])->send(new \App\Mail\LpoutCreatedMail($lpoout, $generatedPdfPath));
                            Log::info("GeneratePdfReportJob: LPO email dispatched to {$this->options['email_to']}");
                        }
                    }
                    break;

                case 'invoice':
                    $invoice = Invoice::find($this->entityId);
                    if ($invoice) {
                        $pdfService = new InvoicePdfService();
                        $letterhead = $this->options['letterhead'] ?? 'fts';
                        $generatedPdfPath = $pdfService->generateInvoicePdf($invoice, $letterhead);
                        Log::info("GeneratePdfReportJob: Invoice PDF generated at {$generatedPdfPath}");
                    }
                    break;

                case 'payroll':
                    $payroll = StaffPayroll::find($this->entityId);
                    if ($payroll) {
                        $pdfService = new StaffPayrollPdfService();
                        $generatedPdfPath = $pdfService->generatePayslipPdf($payroll);
                        Log::info("GeneratePdfReportJob: Payroll PDF generated at {$generatedPdfPath}");
                    }
                    break;

                default:
                    Log::warning("GeneratePdfReportJob: Unknown report type '{$this->reportType}'");
            }
        } catch (\Throwable $e) {
            Log::error('GeneratePdfReportJob failed during PDF rendering', [
                'type'      => $this->reportType,
                'entity_id' => $this->entityId,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GeneratePdfReportJob: permanently failed', [
            'type'      => $this->reportType,
            'entity_id' => $this->entityId,
            'error'     => $exception->getMessage(),
        ]);
    }
}
