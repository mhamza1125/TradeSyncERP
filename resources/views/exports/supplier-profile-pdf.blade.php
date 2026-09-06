<!DOCTYPE html>
<html lang="en">
<head>
<title>Supplier Profile — {{ $supplier->name }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

@php
    $locationLabel = collect([$supplier->city, $supplier->country])->filter()->implode(', ') ?: '—';
@endphp

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Supplier Profile</div>
                <div class="db-sub">{{ $supplier->name }}</div>
            </td>
            <td class="db-right">
                @if($supplier->status)
                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Active</span>
                @else
                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Inactive</span>
                @endif
                <div class="db-date" style="margin-top:6px;">Since {{ $supplier->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Headline figures --}}
<table class="stat-row">
    <tr>
        <td style="width:33.33%;">
            <div class="stat-tile">
                <span class="stat-label">Location</span>
                <span class="stat-value stat-value-sm">{{ $locationLabel }}</span>
            </div>
        </td>
        <td style="width:33.33%;">
            <div class="stat-tile">
                <span class="stat-label">Linked Customers</span>
                <span class="stat-value">{{ $supplier->customers->count() }}</span>
            </div>
        </td>
        <td style="width:33.34%;">
            <div class="stat-tile">
                <span class="stat-label">Total Samples</span>
                <span class="stat-value">{{ $supplier->samples->count() }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- Contact + business details, side by side --}}
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:50%; vertical-align:top; padding-right:8px;">
            <div class="info-panel">
                <div class="info-panel-header">Contact Information</div>
                <div class="info-panel-body">
                    <table class="info-grid">
                        <tr><td class="info-label">Supplier Name</td><td class="info-value">{{ $supplier->name }}</td></tr>
                        <tr><td class="info-label">Address</td><td class="info-value">{{ $supplier->address ?? '—' }}</td></tr>
                        <tr><td class="info-label">City</td><td class="info-value">{{ $supplier->city ?? '—' }}</td></tr>
                        <tr><td class="info-label">Country</td><td class="info-value">{{ $supplier->country ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
        <td style="width:50%; vertical-align:top; padding-left:8px;">
            <div class="info-panel">
                <div class="info-panel-header">Business Details</div>
                <div class="info-panel-body">
                    <table class="info-grid">
                        <tr><td class="info-label">Status</td><td class="info-value">{{ $supplier->status ? 'Active' : 'Inactive' }}</td></tr>
                        <tr><td class="info-label">Registered</td><td class="info-value">{{ $supplier->created_at->format('d M Y') }}</td></tr>
                        <tr><td class="info-label">Linked Customers</td><td class="info-value">{{ $supplier->customers->count() }}</td></tr>
                        <tr><td class="info-label">Total Samples</td><td class="info-value">{{ $supplier->samples->count() }}</td></tr>
                    </table>
                </div>
            </div>
            @if($supplier->remarks)
            <div class="info-panel">
                <div class="info-panel-header">Remarks</div>
                <div class="info-panel-body">
                    <p>{{ $supplier->remarks }}</p>
                </div>
            </div>
            @endif
        </td>
    </tr>
</table>

@if($supplier->customers->count())
<div class="info-panel">
    <div class="info-panel-header">Linked Customers</div>
    <div class="info-panel-body" style="padding:0;">
        <table class="data-table data-table-fixed">
            <thead>
                <tr>
                    <th style="width:6%">#</th>
                    <th style="width:70%">Brand / Customer</th>
                    <th style="width:24%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplier->customers as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="fw-bold">{{ Illuminate\Support\Str::limit($c->display_name, 40) }}</td>
                    <td><span class="badge badge-{{ $c->status ? 'success' : 'danger' }}">{{ $c->status ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</body>
</html>
