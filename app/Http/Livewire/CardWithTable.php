<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class CardWithTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $project;
    public $title;
    public $search = '';

    public function mount($project, $title)
    {
        $this->project = $project;
        $this->title = $title;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $records = $this->getRecords();

        return view('livewire.card-with-table', [
            'records' => $records,
            'title' => $this->title,
            'normalizedType' => $this->getNormalizedType(),
        ]);
    }

    public function getNormalizedType(): string
    {
        return str_replace([' ', '-'], '_', (string) $this->title);
    }

    public function getRecords()
    {
        try {
            $type = $this->getNormalizedType();
            if (method_exists($this, $type)) {
                return $this->$type();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("CardWithTable error for {$this->title}: " . $e->getMessage());
        }

        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    }

    public function Invoices()
    {
        $query = \App\Models\Invoice::whereHas('quotation.project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('invoice_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Lpoins()
    {
        $query = \App\Models\Lpoin::with('quotation')->whereHas('quotation.project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('ref_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Extensions()
    {
        $query = \App\Models\ProjectExtension::with('quotation')->where('project_id', $this->project);
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Receipts()
    {
        $query = \App\Models\Receipt::with(['transaction_payment_type'])->whereHas('project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('total', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Lpoouts()
    {
        $query = \App\Models\Lpoout::where('project_id', $this->project)->where('is_latest_revision', true);
        if ($this->search) {
            $query->where('total_amount', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Petty_Cashes()
    {
        $query = \App\Models\PettyCash::with('payment_type')->where('project_id', $this->project);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('total_amount', 'like', '%' . $this->search . '%');
            });
        }
        return $query->latest('id')->paginate(10);
    }

    public function Payments()
    {
        $query = \App\Models\Payment::with(['transaction_payment_type'])
            ->where('transactionable_type', 'App\Models\PaymentInvoice')
            ->whereHasMorph('transactionable', [\App\Models\PaymentInvoice::class], function ($q) {
                $q->where('project_id', $this->project);
            });
        if ($this->search) {
            $query->where('total', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Extension_Invoices()
    {
        $query = \App\Models\Invoice::whereHas('quotation.extension_project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('invoice_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Extension_Lpoins()
    {
        $query = \App\Models\Lpoin::whereHas('quotation.extension_project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('ref_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Extension_Receipts()
    {
        $query = \App\Models\Receipt::with(['transaction_payment_type'])->whereHas('extension_project', function ($q) {
            $q->where('projects.id', $this->project);
        });
        if ($this->search) {
            $query->where('total', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Payment_invoices()
    {
        $query = \App\Models\PaymentInvoice::where('vendor_id', $this->project);
        if ($this->search) {
            $query->where('invoice_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Vendor_Payments()
    {
        $query = \App\Models\Payment::with(['transaction_payment_type', 'transactionable'])
            ->where('transactionable_type', 'App\Models\PaymentInvoice')
            ->whereHasMorph('transactionable', [\App\Models\PaymentInvoice::class], function ($q) {
                $q->where('vendor_id', $this->project);
            });
        if ($this->search) {
            $query->where('total', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }

    public function Vendor_Lpoouts()
    {
        $query = \App\Models\Lpoout::with(['vendor', 'lpo_out_type'])
            ->where('vendor_id', $this->project);
        if ($this->search) {
            $query->where('lpo_invoice_no', 'like', '%' . $this->search . '%');
        }
        return $query->latest('id')->paginate(10);
    }
}
