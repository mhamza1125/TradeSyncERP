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

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:24%">Account Name</th>
            <th style="width:11%">Type</th>
            <th style="width:19%">Bank</th>
            <th style="width:16%">Account Number</th>
            <th class="text-right" style="width:15%">Opening Balance</th>
            <th style="width:11%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $i => $account)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($account->account_name, 26) }}</td>
            <td class="text-center">
                <span class="badge {{ $account->account_type === 'Bank' ? 'badge-info' : 'badge-secondary' }}">
                    {{ $account->account_type }}
                </span>
            </td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($account->bank?->bank_name ?? '—', 18) }}</td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($account->account_number ?? '—', 16) }}</td>
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
