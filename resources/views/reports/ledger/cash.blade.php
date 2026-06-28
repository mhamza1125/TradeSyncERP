@extends('index')

@section('title', 'Cash Ledger - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Cash Ledger</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item">Cash Ledger</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                        <i class="feather-filter"></i>
                    </a>
                    <a href="{{ route('ledger.cash.export-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i>Export PDF
                    </a>
                    @can('transfers.create')
                    <a href="{{ route('transfers.create') }}" class="btn btn-primary">
                        <i class="feather-repeat me-2"></i>Transfer Funds
                    </a>
                    @endcan
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse {{ request()->hasAny(['account_id','from_date','to_date']) ? 'show' : '' }} page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('ledger.cash') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="account_id" class="form-select">
                            @foreach($accounts as $a)
                            <option value="{{ $a->id }}" @selected(request('account_id') == $a->id || (!request('account_id') && $account && $account->id === $a->id))>
                                {{ $a->account_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search me-1"></i>Apply</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('ledger.cash') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @if($accounts->isEmpty())
        <div class="alert alert-warning">No cash accounts found. Please create a Cash account in <a href="{{ route('masters.accounts.create') }}">Masters → Accounts</a>.</div>
        @else

        {{-- Account Summary Card --}}
        @if($account)
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-0 bg-soft-primary">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg bg-primary text-white rounded">
                                <i class="feather-dollar-sign fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Account</div>
                                <div class="fw-bold fs-6">{{ $account->account_name }}</div>
                                <div class="text-muted small">Opening Balance: <span class="fw-semibold text-dark">{{ number_format($account->opening_balance, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="cashLedger">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:110px">Date</th>
                                        <th>Type</th>
                                        <th>Reference</th>
                                        <th>Particulars</th>
                                        <th class="text-end">Debit (Dr)</th>
                                        <th class="text-end">Credit (Cr)</th>
                                        <th class="text-end">Balance</th>
                                        <th>Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalDr = 0; $totalCr = 0; $balance = $openingBalance; @endphp
                                    @forelse($transactions as $txn)
                                    @php
                                        // JournalEntry (transfers) uses actual debit/credit IDs recorded correctly.
                                        // All other types were recorded with the same account on both sides (data bug),
                                        // so derive the column from transaction_type instead.
                                        if ($txn->transaction_type === 'JournalEntry') {
                                            $isDr = $txn->debit_account_id == $account->id && $txn->credit_account_id != $account->id;
                                            $isCr = $txn->credit_account_id == $account->id && $txn->debit_account_id != $account->id;
                                            $other = $isDr ? $txn->creditAccount : $txn->debitAccount;
                                        } elseif ($txn->transaction_type === 'CustomerReceipt') {
                                            $isDr = true; $isCr = false;
                                            $other = null;
                                        } else {
                                            // Expense, Salary, VendorPayment — money going OUT
                                            $isDr = false; $isCr = true;
                                            $other = null;
                                        }
                                        if ($isDr) { $totalDr += $txn->amount; $balance += $txn->amount; }
                                        if ($isCr) { $totalCr += $txn->amount; $balance -= $txn->amount; }
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{ $txn->transaction_date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-soft-secondary text-secondary">{{ $txn->transaction_type ?? '—' }}</span>
                                        </td>
                                        <td class="text-muted small">
                                            @if($txn->reference_type && $txn->reference_id)
                                                {{ $txn->reference_type }} #{{ $txn->reference_id }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            {{ $other->account_name ?? '—' }}
                                            @if($txn->remarks)
                                                <div class="text-muted small">{{ $txn->remarks }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold {{ $isDr ? 'text-success' : 'text-muted' }}">
                                            {{ $isDr ? number_format($txn->amount, 2) : '—' }}
                                        </td>
                                        <td class="text-end fw-semibold {{ $isCr ? 'text-danger' : 'text-muted' }}">
                                            {{ $isCr ? number_format($txn->amount, 2) : '—' }}
                                        </td>
                                        <td class="text-end fw-semibold {{ $balance >= 0 ? 'text-dark' : 'text-danger' }}">
                                            {{ number_format($balance, 2) }}
                                        </td>
                                        <td class="text-muted small">{{ $txn->creator->name ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="feather-inbox fs-1 d-block mb-2"></i>
                                            No transactions found for the selected period.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->count() > 0)
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Page Totals:</td>
                                        <td class="text-end text-success">{{ number_format($totalDr, 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($totalCr, 2) }}</td>
                                        <td class="text-end {{ $balance >= 0 ? 'text-dark' : 'text-danger' }}">{{ number_format($balance, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions</small>
                        {{ $transactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
