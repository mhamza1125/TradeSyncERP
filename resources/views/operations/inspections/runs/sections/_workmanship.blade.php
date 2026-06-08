{{-- Workmanship Check — quality checklist for the run's sample --}}
@php
    $sample = $run->sample;
    $items  = $runSection->data['items'] ?? [];
@endphp

{{-- Sample info bar --}}
@if($sample)
<div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-4">
    <i class="feather-package text-primary fs-4"></i>
    <div>
        <div class="fw-semibold fs-14">{{ $sample->sample_code }}
            @if($sample->product_name)
                <span class="text-muted fw-normal">— {{ $sample->product_name }}</span>
            @endif
        </div>
        <div class="fs-12 text-muted d-flex gap-2">
            @if($sample->customer)
                <span>{{ $sample->customer->customer_name }}</span>
            @endif
            @if($sample->category)
                <span class="badge bg-soft-primary text-primary">{{ $sample->category->category_name }}</span>
            @endif
        </div>
    </div>
</div>
@else
<div class="alert alert-soft-warning mb-4">No sample linked to this run.</div>
@endif

{{-- Checklist items --}}
@if(count($items))
<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">#</th>
                <th>Inspection Item</th>
                <th style="width:240px">Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
            @php
                $itemResult = old("sections.{$runSection->id}.data.items.{$idx}.status",
                    $item['status'] ?? null);
            @endphp
            <tr>
                <td class="text-muted ps-3">{{ $idx + 1 }}</td>
                <td class="fw-semibold fs-13">
                    {{ $item['name'] ?? "Item " . ($idx + 1) }}
                    @if(!empty($item['required']))
                        <span class="text-danger ms-1" title="Required">*</span>
                    @endif
                    @if(!empty($item['description']))
                        <small class="d-block text-muted fw-normal">{{ $item['description'] }}</small>
                    @endif
                </td>
                <td>
                    @include('operations.inspections.runs.sections._result_toggle', [
                        'name'  => "sections[{$runSection->id}][data][items][{$idx}][status]",
                        'value' => $itemResult,
                    ])
                    {{-- Preserve item name/required through form submission --}}
                    <input type="hidden" name="sections[{{ $runSection->id }}][data][items][{{ $idx }}][name]"
                           value="{{ $item['name'] ?? '' }}">
                    @if(!empty($item['required']))
                    <input type="hidden" name="sections[{{ $runSection->id }}][data][items][{{ $idx }}][required]"
                           value="1">
                    @endif
                    @if(!empty($item['description']))
                    <input type="hidden" name="sections[{{ $runSection->id }}][data][items][{{ $idx }}][description]"
                           value="{{ $item['description'] }}">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="text-center py-4 text-muted">
    <i class="feather-sliders fs-2 d-block mb-2 opacity-50"></i>
    <p class="mb-0 fs-13">No checklist items defined for this section.</p>
    <small>Edit the inspection section template to add items.</small>
</div>
@endif
