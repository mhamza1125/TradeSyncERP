<!DOCTYPE html>
<html lang="en">
<head>
<title>Currencies</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Currencies</div>
                <div class="db-sub">{{ $currencies->count() }} currenc{{ $currencies->count() !== 1 ? 'ies' : 'y' }} listed</div>
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
            <th style="width:12%">Code</th>
            <th style="width:32%">Currency Name</th>
            <th class="text-right" style="width:18%">Exchange Rate</th>
            <th style="width:17%; text-align:center;">Default</th>
            <th style="width:17%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($currencies as $i => $currency)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $currency->currency_code }}</td>
            <td>{{ Illuminate\Support\Str::limit($currency->currency_name, 28) }}</td>
            <td class="text-right">{{ number_format($currency->exchange_rate, 4) }}</td>
            <td class="text-center">
                @if($currency->is_default)
                    <span class="badge badge-primary">Default</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td class="text-center">
                <span class="badge {{ $currency->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $currency->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="no-data">No currencies found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $currencies->count() }} records
</div>

</body>
</html>
