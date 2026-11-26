{{-- Body of one report section. Handles every section_type (docs: CLAUDE.md, 19 values).
     Params (inherited): $rs, $run, $report, $imgBase64, $secNo. Photos are printed by the caller. --}}
@php
    $sec     = $rs->section;
    $secType = $sec->section_type;
    $slug    = $sec->slug;
    $data    = $rs->data ?? [];
    $colHas  = fn ($rows, $key) => collect($rows)->contains(fn ($row) => trim((string) ($row[$key] ?? '')) !== '');
@endphp

@switch($secType)

{{-- ── General information ─────────────────────────────────────────────── --}}
@case('general_info')
    @include('exports.inspection-report._kv', ['fields' => [
        'Buyer / Client'      => $data['buyer_name'] ?? null,
        'Factory / Supplier'  => $data['factory_name'] ?? null,
        'PO / Order Number'   => $data['po_number'] ?? null,
        'Style / Article No.' => $data['style_article_no'] ?? null,
        'Order Quantity'      => $data['order_quantity'] ?? null,
        'Inspection Date'     => $data['inspection_date'] ?? null,
        'Inspection Location' => $data['inspection_location'] ?? null,
        'Inspector'           => $data['inspector_name'] ?? null,
        'Product Description' => $data['product_description'] ?? null,
    ]])
    @break

{{-- ── Quantity & sampling count ───────────────────────────────────────── --}}
@case('quantity_sampling')
    @include('exports.inspection-report._kv', ['fields' => [
        'Product Quantity'        => $data['product_quantity'] ?? null,
        'Packed Goods Quantity'   => $data['packed_goods_qty'] ?? null,
        'Packed Cartons Quantity' => $data['packed_cartons_qty'] ?? null,
    ]])
    @break

{{-- ── AQL sampling ────────────────────────────────────────────────────── --}}
@case('aql')
    @php $aql = $run->aql; @endphp
    @if(!$aql)
        <div class="empty">No AQL sampling data recorded.</div>
    @else
        @php
            $aqlTone = $report->tone($aql->verdict);
            $fullInspection = $aql->sample_size && $aql->lot_size && $aql->sample_size >= $aql->lot_size;
        @endphp
        <table class="aql-head">
            <tr>
                <td class="aql-fig"><span class="fig-l">Lot size</span><span class="fig-v">{{ number_format($aql->lot_size ?? 0) }}</span><span class="fig-s">units</span></td>
                <td class="aql-fig"><span class="fig-l">Sample size</span><span class="fig-v">{{ $aql->sample_size ? number_format($aql->sample_size) : '—' }}</span><span class="fig-s">units inspected</span></td>
                <td class="aql-fig"><span class="fig-l">Inspection level</span><span class="fig-v">{{ $aql->inspection_level ?? '—' }}</span><span class="fig-s">code letter {{ $aql->code_letter ?? '—' }}</span></td>
                <td class="aql-res tone-{{ $aqlTone }}"><span class="fig-l">AQL result</span><span class="fig-v">{{ strtoupper($aql->verdict ?? 'Pending') }}</span></td>
            </tr>
        </table>
        <table class="tbl aql-table">
            <thead>
                <tr>
                    <th style="width:16%">Defect class</th>
                    <th style="width:12%" class="c">AQL</th>
                    <th style="width:10%" class="c">Code</th>
                    <th style="width:13%" class="c">Sample (n)</th>
                    <th style="width:12%" class="c">Accept (Ac)</th>
                    <th style="width:12%" class="c">Reject (Re)</th>
                    <th style="width:11%" class="c">Found</th>
                    <th style="width:14%" class="c">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['critical' => 'Critical', 'major' => 'Major', 'minor' => 'Minor'] as $key => $label)
                @php
                    $aqlLevel = $aql->{"aql_{$key}"};
                    $ac       = $aql->{"ac_{$key}"};
                    $re       = $aql->{"re_{$key}"};
                    $found    = $aql->{"found_{$key}"} ?? 0;
                    // No Ac (no plan for this severity) can never print PASS (docs/QC_RULES.md §2).
                    $rowRes   = $ac === null ? '—' : ($found > $ac ? 'FAIL' : 'PASS');
                @endphp
                <tr class="{{ $rowRes === 'FAIL' ? 'row-fail' : '' }}">
                    <td class="aql-cat">{{ $label }}</td>
                    <td>{{ $aqlLevel === null ? '—' : ((float) $aqlLevel === -1.0 ? 'Not allowed' : $aqlLevel.'%') }}</td>
                    <td>{{ $aql->{"code_letter_{$key}"} ?? '—' }}</td>
                    <td>{{ $aql->{"sample_size_{$key}"} ?? '—' }}</td>
                    <td>{{ $ac ?? '—' }}</td>
                    <td>{{ $re ?? '—' }}</td>
                    <td class="b">{{ $found }}</td>
                    <td>@if($rowRes === '—')<span class="muted">—</span>@else<span class="chip chip-{{ strtolower($rowRes) }}">{{ $rowRes }}</span>@endif</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Sampling basis: the plan actually applied (docs/QC_RULES.md §1, rule 8) --}}
        <div class="fineprint">
            <strong>Sampling basis:</strong> {{ $aql->standard_ref ?? \App\Services\Inspection\AqlCalculationService::STANDARD_REF }};
            normal inspection, level {{ $aql->inspection_level ?? '—' }}; switching rules not applied.
            @if($fullInspection) 100% inspection (sample equals lot). @endif
            Found counts {{ $aql->counts_source === 'defect_rows' ? 'counted from the recorded defects' : 'entered by the inspector' }}.
        </div>

        @if(!empty($aql->variations))
        <div class="sub-h">Quantity distribution by variation</div>
        <table class="tbl">
            <thead><tr><th style="width:35%">Colour</th><th style="width:25%">Size</th><th style="width:20%" class="c">Order qty</th><th style="width:20%" class="c">Inspected qty</th></tr></thead>
            <tbody>
                @foreach($aql->variations as $var)
                <tr><td>{{ $var['color'] ?? '—' }}</td><td>{{ $var['size'] ?? '—' }}</td><td class="c">{{ $var['order_qty'] ?? '—' }}</td><td class="c">{{ $var['inspect_qty'] ?? '—' }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($aql->sizeBreakdowns->isNotEmpty())
        <div class="sub-h">Checked and defective units per size</div>
        <table class="tbl">
            <thead><tr><th style="width:31%">Size</th><th style="width:23%" class="c">Order qty</th><th style="width:23%" class="c">Checked qty</th><th style="width:23%" class="c">Defective qty</th></tr></thead>
            <tbody>
                @foreach($aql->sizeBreakdowns as $sb)
                <tr><td>{{ $sb->size_label }}</td><td class="c">{{ $sb->order_qty }}</td><td class="c">{{ $sb->checked_qty }}</td><td class="c">{{ $sb->error_qty }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @endif
    @endif
    @break

{{-- ── Defects found ───────────────────────────────────────────────────── --}}
@case('defects')
    @php $dc = $report->defectCounts(collect([$rs])); @endphp
    @if($rs->defects->isEmpty())
        <div class="empty">No defects were found.</div>
    @else
        <table class="sev-strip">
            <tr>
                <td><div class="sev-box sev-critical"><span class="sev-n">{{ $dc['critical'] }}</span><span class="sev-l">Critical</span></div></td>
                <td><div class="sev-box sev-major"><span class="sev-n">{{ $dc['major'] }}</span><span class="sev-l">Major</span></div></td>
                <td><div class="sev-box sev-minor"><span class="sev-n">{{ $dc['minor'] }}</span><span class="sev-l">Minor</span></div></td>
                @if($dc['functional'] > 0)
                <td><div class="sev-box sev-functional"><span class="sev-n">{{ $dc['functional'] }}</span><span class="sev-l">Functional</span></div></td>
                @endif
            </tr>
        </table>
        @php
            $dRows = $rs->defects;
            $dShowSize = $dRows->contains(fn ($r) => trim((string) $r->size) !== '');
            $dShowCarton = $dRows->contains(fn ($r) => trim((string) $r->carton_no) !== '');
            $dShowDisp = $dRows->contains(fn ($r) => trim((string) $r->disposition_code) !== '');
        @endphp
        <table class="tbl defect-table">
            <thead>
                <tr>
                    <th style="width:5%" class="c">#</th>
                    <th style="width:{{ 54 - ($dShowSize ? 8 : 0) - ($dShowCarton ? 9 : 0) - ($dShowDisp ? 8 : 0) }}%">Defect</th>
                    <th style="width:12%" class="c">Class</th>
                    <th style="width:7%" class="c">Qty</th>
                    @if($dShowSize)<th style="width:8%" class="c">Size</th>@endif
                    @if($dShowCarton)<th style="width:9%" class="c">Carton</th>@endif
                    @if($dShowDisp)<th style="width:8%" class="c">Disp.</th>@endif
                    <th style="width:22%">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dRows as $i => $row)
                @php
                    $sev = $report->severityOf($row);
                    $rowPhotos = $report->defectRowPhotos($rs, $row);
                @endphp
                <tr>
                    <td class="c muted">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $report->defectName($row) }}</strong>
                        @if($row->notes)<div class="small">{{ $row->notes }}</div>@endif
                        @if($row->status && $row->status !== 'open')<div class="small muted">Status: {{ ucfirst($row->status) }}</div>@endif
                    </td>
                    <td class="c"><span class="chip chip-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td class="c b">{{ $row->qty ?? 1 }}</td>
                    @if($dShowSize)<td class="c">{{ $row->size ?: '—' }}</td>@endif
                    @if($dShowCarton)<td class="c">{{ $row->carton_no ?: '—' }}</td>@endif
                    @if($dShowDisp)<td class="c">{{ $row->disposition_code ?: '—' }}</td>@endif
                    <td>
                        @forelse($rowPhotos as $rowPhoto)
                            @php $rowB64 = $imgBase64($rowPhoto->file_path); @endphp
                            @if($rowB64)<img src="{{ $rowB64 }}" class="defect-thumb" alt="">@else<span class="small muted">Image unavailable</span>@endif
                        @empty
                            <span class="muted">—</span>
                        @endforelse
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @break

{{-- ── Checklists (packing, labels, variations, barcode, carton dimensions, …) ── --}}
@case('checklist')
    @php
        $skipKeys = ['items', 'cartons', 'dim_unit', 'weight_unit', 'barcode_status', 'tech_pack_reference', 'result', 'notes', 'remarks'];
        $extra = collect($data)->except($skipKeys)->filter(fn ($v) => $v !== null && $v !== '' && !is_array($v))
            ->mapWithKeys(fn ($v, $k) => [ucwords(str_replace('_', ' ', $k)) => $v])->all();
    @endphp
    @include('exports.inspection-report._kv', ['fields' => $extra])

    @if($slug === 'barcode_testing')
        @php
            $bStatus = $data['barcode_status'] ?? null;
            $bTone = match($bStatus) { 'functional' => 'pass', 'non-functional' => 'fail', 'partial' => 'conditional', default => 'na' };
        @endphp
        @if($bStatus)
        <table class="kv"><tr><td class="kv-k">Barcode scan</td><td class="kv-v"><span class="chip chip-{{ $bTone }}">{{ ucfirst($bStatus) }}</span></td><td class="kv-k"></td><td class="kv-v"></td></tr></table>
        @endif

    @elseif($slug === 'carton_dimensions_weight')
        @php
            $cartons = collect($data['cartons'] ?? [])->filter(fn ($c) => collect($c)->except('carton_type')->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty())->values();
            $dimUnit = $data['dim_unit'] ?? 'cm';
            $wUnit = $data['weight_unit'] ?? 'kg';
            $cols = array_filter([
                'length' => $colHas($cartons, 'length') ? "Length ({$dimUnit})" : null,
                'width' => $colHas($cartons, 'width') ? "Width ({$dimUnit})" : null,
                'height' => $colHas($cartons, 'height') ? "Height ({$dimUnit})" : null,
                'gross_weight' => $colHas($cartons, 'gross_weight') ? "Gross wt. ({$wUnit})" : null,
                'net_weight' => $colHas($cartons, 'net_weight') ? "Net wt. ({$wUnit})" : null,
            ]);
            $cShowRemarks = $colHas($cartons, 'remarks');
        @endphp
        @if($cartons->isNotEmpty())
        <table class="tbl">
            <thead>
                @php $cw = count($cols) ? round((100 - 24 - ($cShowRemarks ? 22 : 0)) / count($cols), 2) : 0; @endphp
                <tr>
                    <th style="width:{{ count($cols) ? 24 : ($cShowRemarks ? 78 : 100) }}%">Carton</th>
                    @foreach($cols as $label)<th class="c" style="width:{{ $cw }}%">{{ $label }}</th>@endforeach
                    @if($cShowRemarks)<th style="width:22%">Remarks</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($cartons as $ci => $carton)
                <tr>
                    <td class="b">{{ ($carton['carton_type'] ?? '') ?: 'Carton '.($ci + 1) }}</td>
                    @foreach($cols as $key => $label)<td class="c">{{ ($carton[$key] ?? '') !== '' ? $carton[$key] : '—' }}</td>@endforeach
                    @if($cShowRemarks)<td class="small">{{ $carton['remarks'] ?? '' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @else
        @if($slug === 'variations_techpack' && !empty($data['tech_pack_reference']))
        <table class="kv"><tr><td class="kv-k">Tech pack reference</td><td class="kv-v">{{ $data['tech_pack_reference'] }}</td><td class="kv-k"></td><td class="kv-v"></td></tr></table>
        @endif
        @include('exports.inspection-report._results-table', ['rows' => $report->resultRows($rs), 'itemLabel' => 'Checkpoint'])
    @endif

    @if(!empty($data['remarks']) && is_string($data['remarks']))<div class="note">{{ $data['remarks'] }}</div>@endif
    @if(!empty($data['notes']) && is_string($data['notes']) && $slug !== 'variations_techpack')<div class="note">{{ $data['notes'] }}</div>@endif
    @break

{{-- ── Checkpoints / task lists (packing check, functional test, …) ────── --}}
@case('checkpoint')
@case('task_list')
    @include('exports.inspection-report._results-table', ['rows' => $report->resultRows($rs), 'itemLabel' => 'Checkpoint'])
    @break

{{-- ── Workmanship (legacy) ────────────────────────────────────────────── --}}
@case('workmanship')
    @include('exports.inspection-report._results-table', ['rows' => $report->resultRows($rs), 'itemLabel' => 'Item'])
    @break

{{-- ── Verification (seal, shipment) ───────────────────────────────────── --}}
@case('verification')
    @php
        $verExtra = collect($data)->except('items')->filter(fn ($v) => $v !== null && $v !== '' && !is_array($v))
            ->mapWithKeys(fn ($v, $k) => [ucwords(str_replace('_', ' ', $k)) => $v])->all();
    @endphp
    @include('exports.inspection-report._kv', ['fields' => $verExtra])
    @include('exports.inspection-report._results-table', ['rows' => $report->resultRows($rs), 'itemLabel' => 'Verification item'])
    @break

{{-- ── Article results ─────────────────────────────────────────────────── --}}
@case('article_results')
    @php
        $articles = collect($data['articles'] ?? [])->filter(fn ($a) => !empty($a['article_no']) || !empty($a['result']))->values();
        $aShowSize = $colHas($articles, 'size');
    @endphp
    @if($articles->isEmpty())
        <div class="empty">No article results recorded.</div>
    @else
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:6%" class="c">#</th>
                <th style="width:{{ $aShowSize ? 24 : 30 }}%">Article / style no.</th>
                <th style="width:{{ $aShowSize ? 16 : 22 }}%">Colour</th>
                @if($aShowSize)<th style="width:12%">Size</th>@endif
                <th style="width:14%" class="c">Qty ordered</th>
                <th style="width:14%" class="c">Qty inspected</th>
                <th style="width:14%" class="c">Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($articles as $aIdx => $ai)
            @php $ak = \App\Services\Inspection\InspectionReportService::resultKey($ai['result'] ?? ''); @endphp
            <tr class="{{ $ak === 'fail' ? 'row-fail' : '' }}">
                <td class="c muted">{{ $aIdx + 1 }}</td>
                <td class="b">{{ $ai['article_no'] ?? '' }}</td>
                <td>{{ $ai['color'] ?? '' }}</td>
                @if($aShowSize)<td>{{ $ai['size'] ?? '' }}</td>@endif
                <td class="c">{{ ($ai['qty_ordered'] ?? '') !== '' ? $ai['qty_ordered'] : '—' }}</td>
                <td class="c">{{ ($ai['qty_inspected'] ?? '') !== '' ? $ai['qty_inspected'] : '—' }}</td>
                <td class="c">@if($ak === '')<span class="muted">Not checked</span>@else<span class="chip chip-{{ match($ak) { 'pass' => 'pass', 'fail' => 'fail', 'na' => 'na', default => 'pending' } }}">{{ match($ak) { 'pass' => 'PASS', 'fail' => 'FAIL', 'na' => 'N/A', default => $ai['result'] } }}</span>@endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @break

{{-- ── Production status ───────────────────────────────────────────────── --}}
@case('production_stages')
    @php
        $stages = collect($data['selections'] ?? [])->filter(fn ($s) => !empty($s['stage']))->values();
        $sPct = $colHas($stages, 'percentage');
        $sQty = $colHas($stages, 'quantity');
        $sNote = $colHas($stages, 'comment');
    @endphp
    @if($stages->isNotEmpty())
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:6%" class="c">#</th>
                <th style="width:{{ 94 - ($sPct ? 15 : 0) - ($sQty ? 16 : 0) - ($sNote ? 33 : 0) }}%">Production stage</th>
                @if($sPct)<th style="width:15%" class="c">Complete</th>@endif
                @if($sQty)<th style="width:16%" class="c">Qty / output</th>@endif
                @if($sNote)<th style="width:33%">Notes</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($stages as $psIdx => $ps)
            <tr>
                <td class="c muted">{{ $psIdx + 1 }}</td>
                <td class="b">{{ $ps['stage'] }}</td>
                @if($sPct)<td class="c">{{ ($ps['percentage'] ?? '') !== '' && $ps['percentage'] !== null ? $ps['percentage'].'%' : '—' }}</td>@endif
                @if($sQty)<td class="c">{{ $ps['quantity'] ?? '—' }}</td>@endif
                @if($sNote)<td class="small">{{ $ps['comment'] ?? '' }}</td>@endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @if(!empty($data['notes']))<div class="note">{{ $data['notes'] }}</div>@endif
    @break

{{-- ── Container loading ───────────────────────────────────────────────── --}}
@case('container')
    @include('exports.inspection-report._kv', ['fields' => [
        'Container Number'      => $data['container_number'] ?? null,
        'Container Type'        => $data['container_type'] ?? null,
        'Seal Number'           => $data['seal_number'] ?? null,
        'Loading Date'          => $data['loading_date'] ?? null,
        'Loading Start'         => $data['loading_start_time'] ?? null,
        'Loading End'           => $data['loading_end_time'] ?? null,
        'Loading Port'          => $data['loading_port'] ?? null,
        'Discharge Port'        => $data['discharge_port'] ?? null,
        'Cartons Loaded'        => $data['total_cartons_loaded'] ?? null,
        'Quantity Loaded'       => $data['total_quantity_loaded'] ?? null,
    ]])
    @if(!empty($data['notes']))<div class="note">{{ $data['notes'] }}</div>@endif
    @break

{{-- ── Selected cartons ────────────────────────────────────────────────── --}}
@case('cartons')
    @php $selCartons = collect($data['cartons'] ?? [])->filter(fn ($c) => array_filter((array) $c))->values(); @endphp
    @if($selCartons->isEmpty())
        <div class="empty">No cartons recorded.</div>
    @else
    <table class="tbl">
        <thead><tr><th style="width:6%" class="c">#</th><th style="width:28%">Carton no.</th><th style="width:20%">Size</th><th style="width:26%">Colour</th><th style="width:20%" class="c">Qty inspected</th></tr></thead>
        <tbody>
            @foreach($selCartons as $cIdx => $carton)
            <tr><td class="c muted">{{ $cIdx + 1 }}</td><td class="b">{{ $carton['box_number'] ?? '' }}</td><td>{{ $carton['size'] ?? '' }}</td><td>{{ $carton['color'] ?? '' }}</td><td class="c">{{ $carton['qty_inspected'] ?? '—' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @break

{{-- ── Reviews (carton condition, corrective action, inspector's assessment, final review) ── --}}
@case('review')
    @if($slug === 'overall_carton_condition')
        @include('exports.inspection-report._kv', ['fields' => ['Overall carton condition' => $data['overall_condition'] ?? '—']])
        @if(!empty($data['remarks']))<div class="note">{{ $data['remarks'] }}</div>@endif

    @elseif($slug === 'corrective_action')
        @php $capItems = collect($data['items'] ?? [])->filter(fn ($c) => !empty($c['defect_description']) || !empty($c['corrective_action']))->values(); @endphp
        @if($capItems->isNotEmpty())
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:5%" class="c">#</th><th style="width:22%">Non-conformance</th><th style="width:18%">Root cause</th>
                    <th style="width:21%">Corrective action</th><th style="width:13%">Responsible</th><th style="width:10%" class="c">Target</th><th style="width:11%" class="c">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($capItems as $capIdx => $cap)
                <tr>
                    <td class="c muted">{{ $capIdx + 1 }}</td>
                    <td>{{ $cap['defect_description'] ?? '' }}</td>
                    <td class="small">{{ $cap['root_cause'] ?? '' }}</td>
                    <td class="small">{{ $cap['corrective_action'] ?? '' }}</td>
                    <td>{{ $cap['responsible_party'] ?? '' }}</td>
                    <td class="c small">{{ $cap['target_date'] ?? '—' }}</td>
                    <td class="c"><span class="chip chip-{{ match($cap['status'] ?? 'Open') { 'Closed' => 'pass', 'Rejected' => 'fail', default => 'pending' } }}">{{ $cap['status'] ?? 'Open' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @elseif($slug === 'overall_article_result')
        {{-- The assessment is printed as recorded, never as a verdict (docs/QC_RULES.md §3). --}}
        @php $asm = $assessmentOf($run); @endphp
        <table class="kv">
            <tr>
                <td class="kv-k">Inspector's assessment</td>
                <td class="kv-v" colspan="3">{{ $data['overall_result'] ?? '—' }} <span class="small muted">(recorded by the inspector; the decision is calculated from the AQL result and the defects)</span></td>
            </tr>
            @if($asm && $asm['differs'])
            <tr><td class="kv-k">Reason the assessment differs</td><td class="kv-v" colspan="3">{{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}</td></tr>
            @endif
            @if(!empty($data['follow_up_date']))
            <tr><td class="kv-k">Follow-up date</td><td class="kv-v" colspan="3">{{ $data['follow_up_date'] }}</td></tr>
            @endif
        </table>
        @if(!empty($data['remarks']))<div class="note">{{ $data['remarks'] }}</div>@endif

    @else
        {{-- final_review or another legacy review slug --}}
        @include('exports.inspection-report._kv', ['fields' => [
            'Action Required' => $data['action_required'] ?? null,
            'Follow-up Date'  => $data['follow_up_date'] ?? null,
            'Inspector'       => $data['inspector_name'] ?? null,
        ]])
        @if(!empty($data['qc_remarks']))<div class="note"><strong>QC remarks:</strong> {{ $data['qc_remarks'] }}</div>@endif
    @endif
    @break

{{-- ── Conclusion ──────────────────────────────────────────────────────── --}}
@case('conclusion')
    @include('exports.inspection-report._kv', ['fields' => [
        'Presented Styles'   => $data['presented_styles'] ?? null,
        'Unpresented Styles' => $data['unpresented_styles'] ?? null,
    ]])
    @if(!empty($data['note']))<div class="note">{{ $data['note'] }}</div>@endif
    @break

{{-- ── Finish ──────────────────────────────────────────────────────────── --}}
@case('finish')
    @include('exports.inspection-report._kv', ['fields' => [
        'Status'         => $run->completed_at ? 'Finished '.$run->completed_at->format('d M Y, H:i') : 'Not finished',
        'Final comments' => $data['comments'] ?? null,
    ]])
    @break

{{-- ── Photo sections (product screening, cover photo) ─────────────────── --}}
@case('images')
@case('cover_photo')
    @if(!empty($data['notes']))<div class="note">{{ $data['notes'] }}</div>@endif
    @break

{{-- ── Files to review ─────────────────────────────────────────────────── --}}
@case('files_review')
    @include('exports.inspection-report._kv', ['fields' => ['Reviewed by inspector' => !empty($data['acknowledged']) ? 'Yes' : 'No']])
    @if(!empty($data['notes']))<div class="note">{{ $data['notes'] }}</div>@endif
    @break

{{-- ── Any other section type ──────────────────────────────────────────── --}}
@default
    @php
        $fbData = collect($data)->except('items')->filter(fn ($v) => $v !== null && $v !== '' && !is_array($v))
            ->mapWithKeys(fn ($v, $k) => [ucwords(str_replace('_', ' ', $k)) => $v])->all();
        $fbRows = collect($data['items'] ?? [])->map(fn ($i) => [
            'label' => (string) ($i['label'] ?? ''), 'result' => trim((string) ($i['result'] ?? '')), 'remarks' => (string) ($i['remarks'] ?? ''),
        ])->all();
    @endphp
    @include('exports.inspection-report._kv', ['fields' => $fbData])
    @include('exports.inspection-report._results-table', ['rows' => $fbRows, 'itemLabel' => 'Item'])
@endswitch
