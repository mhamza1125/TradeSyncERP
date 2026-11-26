{{-- Photo grid of one section. Params: $groups (from InspectionReportService::photoGroups), $imgBase64,
     $photoPrefix (e.g. "2.4"). Fixed 3-column tables; the caption is the row label plus a number, never the file name. --}}
@php $phNo = 0; @endphp
@if(!empty($groups))
<div class="sub-h">Photos</div>
@foreach($groups as $group)
    @if($group['label'])<div class="ph-group">{{ $group['label'] }}</div>@endif
    @foreach($group['images']->chunk(3) as $chunk)
    <table class="ph-grid">
        <tr>
            @foreach($chunk as $img)
            @php $b64 = $imgBase64($img->file_path); $phNo++; @endphp
            <td>
                <div class="ph-frame">
                    @if($b64)<img src="{{ $b64 }}" class="ph-img" alt="" style="{{ $fitImg($b64, 55, 38) }}">@else<div class="ph-missing">Image unavailable</div>@endif
                </div>
                <div class="ph-cap">Photo {{ $photoPrefix }}.{{ $phNo }}{{ $group['label'] ? ' · '.$group['label'] : '' }}</div>
            </td>
            @endforeach
            @for($p = count($chunk); $p < 3; $p++)<td></td>@endfor
        </tr>
    </table>
    @endforeach
@endforeach
@endif
