<!DOCTYPE html>
<html lang="en">
<head>
<title>Supplier Profile — {{ $supplier->name }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

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

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Contact Information</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Supplier Name</td><td class="info-value">{{ $supplier->name }}</td></tr>
                    <tr><td class="info-label">Phone</td><td class="info-value">{{ $supplier->phone ?? '—' }}</td></tr>
                    <tr><td class="info-label">Email</td><td class="info-value">{{ $supplier->email ?? '—' }}</td></tr>
                    <tr><td class="info-label">Address</td><td class="info-value">{{ $supplier->address ?? '—' }}</td></tr>
                    <tr><td class="info-label">City</td><td class="info-value">{{ $supplier->city ?? '—' }}</td></tr>
                    <tr><td class="info-label">Country</td><td class="info-value">{{ $supplier->country ?? '—' }}</td></tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Business Details</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Status</td><td class="info-value">{{ $supplier->status ? 'Active' : 'Inactive' }}</td></tr>
                    <tr><td class="info-label">Registered</td><td class="info-value">{{ $supplier->created_at->format('d M Y') }}</td></tr>
                    <tr><td class="info-label">Linked Customers</td><td class="info-value">{{ $supplier->customers->count() }}</td></tr>
                    <tr><td class="info-label">Total Samples</td><td class="info-value">{{ $supplier->samples->count() }}</td></tr>
                </table>
            </div>
            @if($supplier->remarks)
            <div class="info-section">
                <h3>Remarks</h3>
                <p style="font-size:8.5pt; color:#424242;">{{ $supplier->remarks }}</p>
            </div>
            @endif
        </td>
    </tr>
</table>

@if($supplier->customers->count())
<div class="info-section">
    <h3>Linked Customers</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplier->customers as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $c->customer_name }}</td>
                <td>{{ $c->phone ?? '—' }}</td>
                <td><span class="badge badge-{{ $c->status ? 'success' : 'danger' }}">{{ $c->status ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</body>
</html>
