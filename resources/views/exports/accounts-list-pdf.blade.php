<!DOCTYPE html>
<html lang="en">
<head>
<title>Accounts</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Chart of Accounts</div>
                <div class="db-sub">{{ $accounts->count() }} account{{ $accounts->count() !== 1 ? 's' : '' }} listed</div>
            </td>
            <td class="db-right">
                <div class="db-date">{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px">#</th>
            <th>Account Name</th>
            <th style="width:80px">Type</th>
            <th style="width:140px">Bank</th>
            <th style="width:120px">Account Number</th>
            <th class="text-right" style="width:110px">Opening Balance</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $i => $account)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $account->account_name }}</td>
            <td class="text-center">
                <span class="badge {{ $account->account_type === 'Bank' ? 'badge-info' : 'badge-secondary' }}">
                    {{ $account->account_type }}
                </span>
            </td>
            <td class="text-muted">{{ $account->bank?->bank_name ?? '—' }}</td>
            <td class="text-muted">{{ $account->account_number ?? '—' }}</td>
            <td class="text-right">{{ number_format($account->opening_balance ?? 0, 2) }}</td>
            <td class="text-center">
                <span class="badge {{ $account->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $account->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No accounts found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $accounts->count() }} records
</div>

</body>
</html>
