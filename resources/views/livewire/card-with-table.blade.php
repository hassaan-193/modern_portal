<div>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            @if(isset($records) && method_exists($records, 'total') && $records->total() > 0)
                <small class="text-muted">Showing {{ $records->firstItem() }}-{{ $records->lastItem() }} of {{ $records->total() }}</small>
            @endif
        </div>
        <div class="w-auto">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search...">
        </div>
    </div>

    <div class="table-responsive">
        @if($normalizedType === 'Invoices' || $normalizedType === 'Extension_Invoices')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Amount</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->invoice_no }}</td>
                            <td>{{ $row->start_date }}</td>
                            <td>{{ $row->end_date }}</td>
                            <td>{{ number_format((float)$row->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('invoices.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Lpoins' || $normalizedType === 'Extension_Lpoins')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Date Issue</th>
                        <th>Date Due</th>
                        @if($normalizedType === 'Lpoins')<th>Amount</th>@endif
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->ref_no }}</td>
                            <td>{{ $row->date_issue }}</td>
                            <td>{{ $row->date_due }}</td>
                            @if($normalizedType === 'Lpoins')
                                <td>{{ number_format((float)($row->quotation->contract_value ?? $row->amount ?? 0), 2) }}</td>
                            @endif
                            <td class="text-center">
                                <a href="{{ route('lpoins.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $normalizedType === 'Lpoins' ? 5 : 4 }}" class="text-center text-muted py-3">No LPO In found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Extensions')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Quotation</th>
                        <th>Name</th>
                        <th>Value</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{!! $row->quotation_link !!}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->value }}</td>
                            <td class="text-center">
                                <a href="{{ route('projects.view_extensions', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No extensions found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Receipts' || $normalizedType === 'Extension_Receipts')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        @if($normalizedType === 'Receipts')<th>Status</th>@endif
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date_time }}</td>
                            <td>{{ $row->transaction_payment_type->name ?? '-' }}</td>
                            <td>{{ number_format((float)$row->total, 2) }}</td>
                            @if($normalizedType === 'Receipts')
                                <td>
                                    @include('components.datatables_status', [
                                        'msg' => $row->status ? 'Cleared' : 'Pending Clearance',
                                        'type' => $row->status ? 'success' : 'warning',
                                    ])
                                </td>
                            @endif
                            <td class="text-center">
                                <a href="{{ route('receipts.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $normalizedType === 'Receipts' ? 5 : 4 }}" class="text-center text-muted py-3">No receipts found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Lpoouts')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Total Amount</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td>{!! $row->vendor_link !!}</td>
                            <td>{{ number_format((float)$row->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('lpoouts.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No LPO Out found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Petty_Cashes')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Total Amount</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date_time }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->payment_type->name ?? '-' }}</td>
                            <td>{{ number_format((float)$row->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('pettyCashes.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No petty cash found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Payments')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date_time }}</td>
                            <td>{{ $row->transaction_payment_type->name ?? '-' }}</td>
                            <td>{{ number_format((float)$row->total, 2) }}</td>
                            <td>
                                @include('components.datatables_status', [
                                    'msg' => $row->status ? 'Cleared' : 'Pending Clearance',
                                    'type' => $row->status ? 'success' : 'warning',
                                ])
                            </td>
                            <td class="text-center">
                                <a href="{{ route('payments.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Payment_invoices')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->invoice_no }}</td>
                            <td>{{ $row->start_date }}</td>
                            <td>{{ $row->end_date }}</td>
                            <td>{{ number_format((float)$row->total_amount, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('paymentInvoices.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Vendor_Payments')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date_time }}</td>
                            <td>{{ $row->transaction_payment_type->name ?? '-' }}</td>
                            <td>{{ number_format((float)$row->total, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('payments.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($normalizedType === 'Vendor_Lpoouts')
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>LPO Type</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th style="width: 60px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->lpo_invoice_no }}</td>
                            <td>{{ $row->date }}</td>
                            <td>{!! $row->vendor_link !!}</td>
                            <td>{{ $row->lpo_out_type->name ?? '-' }}</td>
                            <td>{{ number_format((float)$row->total_amount, 2) }}</td>
                            <td>{{ $row->status }}</td>
                            <td class="text-center">
                                <a href="{{ route('lpoouts.show', $row->id) }}" class="btn btn-sm btn-ghost-primary"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No LPO Out found</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <div class="text-center text-muted py-3">No data available for {{ $title }}</div>
        @endif
    </div>

    @if(isset($records) && method_exists($records, 'links') && $records->hasPages())
        <div class="d-flex justify-content-end mt-2">
            {{ $records->links() }}
        </div>
    @endif
</div>
