<!DOCTYPE html>
<html lang="en">
<head>
<title>Product Categories</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Product Categories</div>
                <div class="db-sub">{{ $categories->count() }} categor{{ $categories->count() !== 1 ? 'ies' : 'y' }} listed</div>
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
            <th>Category Name</th>
            <th style="width:200px">Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($categories as $i => $category)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $category->category_name }}</td>
            <td class="text-muted">{{ $category->description ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="3" class="no-data">No categories found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $categories->count() }} records
</div>

</body>
</html>
