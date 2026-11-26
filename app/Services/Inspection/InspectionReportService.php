<?php

namespace App\Services\Inspection;

use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use Illuminate\Support\Collection;

/**
 * Prepares the data the client inspection report (exports/inspection-run-pdf) prints:
 * which sections show, whether a section holds any data, a client-facing result per
 * section, the photos of a section grouped by the row they belong to, defect totals,
 * the key findings list and the cover photo. No verdict is decided here — run
 * decisions and the roll-up stay in InspectionVerdictService (docs/QC_RULES.md §3–4).
 */
class InspectionReportService
{
    /**
     * Slugs treated as "not a section". Must stay identical to the $hiddenSlugs list in
     * resources/views/operations/inspections/runs/edit.blade.php, otherwise stale rows for
     * these slugs leak into the report. The cover photo is still used as the report's photo.
     */
    public const HIDDEN_SLUGS = [
        'corrective_action', 'inspection_conclusion', 'finish_inspection',
        'textile_sample_conformity', 'denim_textile_defects',
        'cover_photo', 'workmanship_check',
    ];

    /** Order used when a section has no sort_order of its own. */
    private const TYPE_PRIORITY = [
        'general_info' => 1, 'images' => 2, 'quantity_sampling' => 3, 'cartons' => 4,
        'cover_photo' => 5, 'checklist' => 6, 'checkpoint' => 7, 'task_list' => 7,
        'files_review' => 8, 'verification' => 9, 'article_results' => 10, 'aql' => 11,
        'defects' => 12, 'workmanship' => 13, 'production_stages' => 14, 'container' => 15,
        'review' => 16, 'conclusion' => 17, 'finish' => 18,
    ];

    /** Severity order for findings (most serious first). */
    private const SEVERITY_RANK = ['critical' => 0, 'major' => 1, 'fail' => 2, 'minor' => 3, 'functional' => 4];

    /** @param  Collection  $defects  Defect masters keyed by id */
    public function __construct(private Collection $defects) {}

    // ── Sections ─────────────────────────────────────────────────────────────

    /** Sections the report prints for a run: not hidden, not skipped (na), in template order. */
    public function visibleSections(InspectionRun $run): Collection
    {
        return $run->runSections
            ->filter(fn ($rs) => $rs->section && ! in_array($rs->section->slug, self::HIDDEN_SLUGS, true) && $rs->status !== 'na')
            ->sortBy(fn ($rs) => $rs->section->sort_order ?? (self::TYPE_PRIORITY[$rs->section->section_type] ?? 99))
            ->values();
    }

    /** True when a section carries no recorded data (matched against real section_type values). */
    public function isEmpty(InspectionRunSection $rs, InspectionRun $run): bool
    {
        $data = $rs->data ?? [];
        $type = $rs->section->section_type;
        $slug = $rs->section->slug;
        $files = $rs->attachments; // any attachment (image or document) counts as recorded evidence
        $has = fn ($v) => $v !== null && $v !== '';

        return match (true) {
            $type === 'general_info' => empty(array_filter([
                $data['buyer_name'] ?? null, $data['factory_name'] ?? null, $data['po_number'] ?? null,
                $data['product_description'] ?? null, $data['order_quantity'] ?? null,
            ], $has)),
            $type === 'images' => $files->isEmpty() && empty($data['notes']),
            $type === 'workmanship' => collect($data['items'] ?? [])->every(fn ($i) => empty($i['status'])),
            $type === 'defects' => $rs->defects->isEmpty(),
            $type === 'aql' => ! $run->aql
                || (($run->aql->found_critical ?? 0) == 0 && ($run->aql->found_major ?? 0) == 0
                    && ($run->aql->found_minor ?? 0) == 0 && ($run->aql->lot_size ?? 0) == 0),
            $type === 'checklist' && $slug === 'carton_dimensions_weight' => collect($data['cartons'] ?? [])
                ->every(fn ($c) => empty(array_filter(collect($c)->except('carton_type')->all(), $has))) && $files->isEmpty(),
            $type === 'checklist' && $slug === 'barcode_testing' => $files->isEmpty() && $rs->status !== 'complete',
            $type === 'checklist' => ! empty($data['items'])
                ? collect($data['items'])->every(fn ($i) => empty($i['result'])) && $files->isEmpty()
                : empty(array_filter(collect($data)->except(['items', 'dim_unit', 'weight_unit'])->all(), fn ($v) => $has($v) && ! is_array($v))) && $files->isEmpty(),
            $type === 'container' => empty(array_filter([
                $data['container_number'] ?? null, $data['seal_number'] ?? null,
                $data['loading_date'] ?? null, $data['notes'] ?? null,
            ], $has)) && $files->isEmpty(),
            $type === 'verification' => empty(array_filter(collect($data)->except('items')->all(), fn ($v) => $has($v) && ! is_array($v)))
                && (empty($data['items']) || collect($data['items'])->every(fn ($i) => empty($i['result'])))
                && $files->isEmpty(),
            $type === 'review' && $slug === 'overall_carton_condition' => empty($data['overall_condition']) && empty($data['remarks']) && $files->isEmpty(),
            $type === 'review' && $slug === 'corrective_action' => collect($data['items'] ?? [])
                ->every(fn ($i) => empty($i['defect_description']) && empty($i['corrective_action'])),
            $type === 'review' && $slug === 'overall_article_result' => empty($data['overall_result']) && empty($data['remarks']),
            $type === 'review' => empty(array_filter([
                $data['overall_verdict'] ?? null, $data['qc_remarks'] ?? null, $data['action_required'] ?? null,
            ], $has)),
            $type === 'article_results' => collect($data['articles'] ?? [])->every(fn ($a) => empty($a['result']) && empty($a['article_no'])),
            $type === 'conclusion' => empty(array_filter([
                $data['presented_styles'] ?? null, $data['unpresented_styles'] ?? null, $data['note'] ?? null,
            ], $has)),
            $type === 'finish' => empty($data['comments']) && ! $run->completed_at,
            $type === 'quantity_sampling' => empty(array_filter([
                $data['product_quantity'] ?? null, $data['packed_goods_qty'] ?? null, $data['packed_cartons_qty'] ?? null,
            ], $has)),
            $type === 'cartons' => collect($data['cartons'] ?? [])->every(fn ($c) => empty(array_filter((array) $c, $has))),
            $type === 'cover_photo' => $files->isEmpty(),
            $type === 'files_review' => $files->isEmpty() && empty($data['notes']) && empty($data['acknowledged']),
            $type === 'checkpoint', $type === 'task_list' => collect($this->taskResults($rs))->every(fn ($t) => $t['result'] === ''),
            $type === 'production_stages' => collect($data['selections'] ?? [])->filter(fn ($s) => ! empty($s['stage']))->isEmpty(),
            default => collect($data)->filter(fn ($v) => $has($v) && ! is_array($v))->isEmpty() && empty($data['items']),
        };
    }

    /**
     * Checkpoint / task-list rows: the template's tasks with the recorded choice.
     *
     * @return array<int, array{key: string, label: string, result: string}>
     */
    public function taskResults(InspectionRunSection $rs): array
    {
        $recorded = $rs->data['tasks'] ?? [];

        return collect($rs->section->default_data['tasks'] ?? [])->map(fn ($t) => [
            'key' => (string) ($t['key'] ?? ''),
            'label' => (string) ($t['label'] ?? $t['key'] ?? ''),
            'result' => trim((string) ($recorded[$t['key'] ?? '']['selected'] ?? '')),
        ])->all();
    }

    /**
     * Pass / Fail / N/A rows of a section, normalised (empty result = not checked).
     *
     * @return array<int, array{label: string, result: string, remarks: string}>
     */
    public function resultRows(InspectionRunSection $rs): array
    {
        $data = $rs->data ?? [];

        return match ($rs->section->section_type) {
            'checkpoint', 'task_list' => array_map(fn ($t) => $t + ['remarks' => ''], $this->taskResults($rs)),
            'workmanship' => collect($data['items'] ?? [])->map(fn ($i) => [
                'label' => (string) ($i['name'] ?? ''), 'result' => trim((string) ($i['status'] ?? '')), 'remarks' => (string) ($i['description'] ?? ''),
            ])->all(),
            'article_results' => collect($data['articles'] ?? [])->filter(fn ($a) => ! empty($a['article_no']) || ! empty($a['result']))->map(fn ($a) => [
                'label' => trim(($a['article_no'] ?? '').' '.($a['color'] ?? '')), 'result' => trim((string) ($a['result'] ?? '')), 'remarks' => (string) ($a['remarks'] ?? ''),
            ])->values()->all(),
            'checklist' => empty($data['items']) && ! empty($data['result'])
                ? [['label' => $rs->section->name, 'result' => trim((string) $data['result']), 'remarks' => (string) ($data['notes'] ?? '')]]
                : collect($data['items'] ?? [])->map(fn ($i) => [
                    'label' => (string) ($i['label'] ?? ''), 'result' => trim((string) ($i['result'] ?? '')), 'remarks' => (string) ($i['remarks'] ?? ''),
                ])->all(),
            'verification' => collect($data['items'] ?? [])->map(fn ($i) => [
                'label' => (string) ($i['label'] ?? ''), 'result' => trim((string) ($i['result'] ?? '')), 'remarks' => (string) ($i['remarks'] ?? ''),
            ])->all(),
            default => [],
        };
    }

    public static function resultKey(?string $result): string
    {
        return match (strtolower(trim((string) $result))) {
            'pass', 'passed', 'ok', 'yes' => 'pass',
            'fail', 'failed', 'no' => 'fail',
            'n/a', 'na' => 'na',
            '' => '',
            default => 'other',
        };
    }

    /**
     * Client-facing result of one section: tone (pass, fail, conditional, pending, info, na)
     * and a short label. Informational sections read "RECORDED"; the inspector's assessment
     * is never shown as PASS/FAIL (docs/QC_RULES.md §3).
     *
     * @return array{tone: string, label: string, detail: string}
     */
    public function sectionResult(InspectionRunSection $rs, InspectionRun $run): array
    {
        $type = $rs->section->section_type;
        $slug = $rs->section->slug;
        $done = $rs->status === 'complete';

        if ($this->isEmpty($rs, $run)) {
            return ['tone' => 'na', 'label' => 'NOT RECORDED', 'detail' => ''];
        }

        if ($type === 'aql') {
            $v = $run->aql?->verdict ?? 'Pending';
            $aql = $run->aql;
            $detail = $aql && $aql->sample_size
                ? number_format($aql->sample_size).' of '.number_format($aql->lot_size ?? 0).' units sampled'
                : 'Sampling plan not complete';

            return ['tone' => $this->tone($v), 'label' => strtoupper($v), 'detail' => $detail];
        }

        if ($type === 'defects') {
            $c = $this->defectCounts(collect([$rs]));
            $total = $c['critical'] + $c['major'] + $c['minor'] + $c['functional'];
            $detail = $this->countsText($c);

            return match (true) {
                $c['critical'] + $c['major'] > 0 => ['tone' => 'fail', 'label' => $total.' FOUND', 'detail' => $detail],
                $total > 0 => ['tone' => 'conditional', 'label' => $total.' FOUND', 'detail' => $detail],
                default => ['tone' => 'pass', 'label' => 'NONE FOUND', 'detail' => 'No defects recorded'],
            };
        }

        if ($slug === 'overall_article_result') {
            return ['tone' => 'info', 'label' => 'RECORDED', 'detail' => "Inspector's assessment: ".($rs->data['overall_result'] ?? '—')];
        }

        if ($slug === 'barcode_testing') {
            return match ($rs->data['barcode_status'] ?? null) {
                'functional' => ['tone' => 'pass', 'label' => 'PASS', 'detail' => 'Barcodes scan correctly'],
                'non-functional' => ['tone' => 'fail', 'label' => 'FAIL', 'detail' => 'Barcodes do not scan'],
                'partial' => ['tone' => 'conditional', 'label' => 'PARTIAL', 'detail' => 'Some barcodes do not scan'],
                default => ['tone' => $done ? 'info' : 'pending', 'label' => $done ? 'RECORDED' : 'IN PROGRESS', 'detail' => ''],
            };
        }

        $rows = $this->resultRows($rs);
        if ($rows !== []) {
            $keys = collect($rows)->map(fn ($r) => self::resultKey($r['result']));
            $pass = $keys->filter(fn ($k) => $k === 'pass')->count();
            $fail = $keys->filter(fn ($k) => $k === 'fail')->count();
            $open = $keys->filter(fn ($k) => $k === '')->count();
            $checked = count($rows) - $open;
            $noun = $type === 'article_results' ? 'article lines' : 'checkpoints';
            $detail = match (true) {
                $fail > 0 => $fail.' of '.$checked.' '.$noun.' failed',
                $checked > 0 && $pass === 0 => 'All '.$checked.' '.$noun.' N/A',
                default => $pass.' of '.$checked.' '.$noun.' passed',
            };
            if ($open > 0) {
                $detail = $checked === 0 ? $open.' not checked' : $detail.' · '.$open.' not checked';
            }

            return match (true) {
                $fail > 0 => ['tone' => 'fail', 'label' => 'FAIL', 'detail' => $detail],
                $open > 0 || ! $done => ['tone' => 'pending', 'label' => 'IN PROGRESS', 'detail' => $detail],
                $pass > 0 => ['tone' => 'pass', 'label' => 'PASS', 'detail' => $detail],
                default => ['tone' => 'na', 'label' => 'N/A', 'detail' => $detail],
            };
        }

        return $done
            ? ['tone' => 'info', 'label' => 'RECORDED', 'detail' => '']
            : ['tone' => 'pending', 'label' => 'IN PROGRESS', 'detail' => ''];
    }

    /** Verdict status → tone used by the report's colour classes. */
    public function tone(?string $status): string
    {
        return match ($status) {
            'Pass' => 'pass',
            'Fail' => 'fail',
            'Conditional' => 'conditional',
            'Re-Inspection' => 'reinspection',
            default => 'pending',
        };
    }

    /** One-line meaning of an overall result, printed under the decision. */
    public function meaning(?string $status): string
    {
        return match ($status) {
            'Pass' => 'The goods inspected meet the inspection criteria.',
            'Fail' => 'The goods inspected do not meet the inspection criteria.',
            'Conditional' => 'Accepted with conditions. See the key findings.',
            'Re-Inspection' => 'A re-inspection is required before a decision can be made.',
            default => 'The inspection is not finished. No decision has been made yet.',
        };
    }

    // ── Defects ──────────────────────────────────────────────────────────────

    public function severityOf($row): string
    {
        return $row->severity ?? ($this->defects->get((int) $row->defect_id)->severity ?? 'minor');
    }

    public function defectName($row): string
    {
        return $this->defects->get((int) $row->defect_id)->defect_name
            ?? $row->defect?->defect_name
            ?? ('Defect #'.$row->defect_id);
    }

    /**
     * Defect quantities by severity across the given run sections (only defect sections
     * count). Quantities are summed, the same way the AQL found counts are (QC_RULES §2).
     *
     * @return array{critical: int, major: int, minor: int, functional: int}
     */
    public function defectCounts(Collection $runSections): array
    {
        $c = ['critical' => 0, 'major' => 0, 'minor' => 0, 'functional' => 0];
        foreach ($runSections as $rs) {
            if ($rs->section?->section_type !== 'defects') {
                continue;
            }
            foreach ($rs->defects as $row) {
                $sev = $this->severityOf($row);
                $c[array_key_exists($sev, $c) ? $sev : 'minor'] += max(1, (int) ($row->qty ?? 1));
            }
        }

        return $c;
    }

    /** @param  iterable<InspectionRun>  $runs */
    public function defectCountsForRuns(iterable $runs): array
    {
        return $this->defectCounts(collect($runs)->flatMap(fn ($r) => $this->visibleSections($r)));
    }

    public function countsText(array $c): string
    {
        $parts = [];
        foreach (['critical' => 'critical', 'major' => 'major', 'minor' => 'minor', 'functional' => 'functional'] as $k => $word) {
            if (($c[$k] ?? 0) > 0) {
                $parts[] = $c[$k].' '.$word;
            }
        }

        return $parts ? implode(', ', $parts) : 'None';
    }

    // ── Key findings ─────────────────────────────────────────────────────────

    /**
     * Every non-conformity in the report, most serious first: defects (summed per defect
     * and article), failed checkpoints, barcode failures and failed AQL results.
     *
     * @param  Collection<int, InspectionRun>  $runs
     * @return array<int, array{article: string, area: string, issue: string, severity: string, qty: ?int}>
     */
    public function findings(Collection $runs): array
    {
        $out = [];
        foreach ($runs as $run) {
            $article = $this->articleLabel($run);
            foreach ($this->visibleSections($run) as $rs) {
                $type = $rs->section->section_type;
                $area = $rs->section->name;

                if ($type === 'defects') {
                    $byDefect = [];
                    foreach ($rs->defects as $row) {
                        $key = $row->defect_id.'|'.$this->severityOf($row);
                        $byDefect[$key] ??= ['name' => $this->defectName($row), 'severity' => $this->severityOf($row), 'qty' => 0, 'notes' => []];
                        $byDefect[$key]['qty'] += max(1, (int) ($row->qty ?? 1));
                        if (trim((string) $row->notes) !== '') {
                            $byDefect[$key]['notes'][] = trim($row->notes);
                        }
                    }
                    foreach ($byDefect as $d) {
                        $out[] = [
                            'article' => $article, 'area' => $area, 'severity' => $d['severity'], 'qty' => $d['qty'],
                            'issue' => $d['name'].($d['notes'] ? ' — '.implode('; ', array_unique($d['notes'])) : ''),
                        ];
                    }

                    continue;
                }

                if ($type === 'aql' && ($run->aql?->verdict ?? null) === 'Fail') {
                    $out[] = ['article' => $article, 'area' => $area, 'severity' => 'fail', 'qty' => null,
                        'issue' => 'AQL limits exceeded ('.$this->countsText([
                            'critical' => (int) $run->aql->found_critical, 'major' => (int) $run->aql->found_major,
                            'minor' => (int) $run->aql->found_minor, 'functional' => 0,
                        ]).' found)'];

                    continue;
                }

                if ($rs->section->slug === 'barcode_testing' && in_array($rs->data['barcode_status'] ?? null, ['non-functional', 'partial'], true)) {
                    $out[] = ['article' => $article, 'area' => $area, 'severity' => 'fail', 'qty' => null,
                        'issue' => ($rs->data['barcode_status'] === 'partial' ? 'Some barcodes do not scan' : 'Barcodes do not scan')
                            .(! empty($rs->data['remarks']) ? ' — '.$rs->data['remarks'] : '')];

                    continue;
                }

                foreach ($this->resultRows($rs) as $row) {
                    if (self::resultKey($row['result']) === 'fail') {
                        $out[] = ['article' => $article, 'area' => $area, 'severity' => 'fail', 'qty' => null,
                            'issue' => $row['label'].($row['remarks'] !== '' ? ' — '.$row['remarks'] : '')];
                    }
                }
            }
        }

        usort($out, fn ($a, $b) => (self::SEVERITY_RANK[$a['severity']] ?? 9) <=> (self::SEVERITY_RANK[$b['severity']] ?? 9));

        return $out;
    }

    // ── Result summary (cover) ───────────────────────────────────────────────

    /**
     * One row per inspection area across all articles, in template order: the worst result
     * of that area and a short detail.
     *
     * Rows with no data in any article carry recorded = false.
     *
     * @return array<int, array{name: string, tone: string, label: string, detail: string, recorded: bool}>
     */
    public function summaryRows(Collection $runs): array
    {
        $rank = ['fail' => 0, 'conditional' => 1, 'pending' => 2, 'pass' => 3, 'info' => 4, 'na' => 5];
        $rows = [];
        $multi = $runs->count() > 1;

        foreach ($runs as $run) {
            foreach ($this->visibleSections($run) as $rs) {
                $name = $rs->section->name;
                $res = $this->sectionResult($rs, $run);
                $rows[$name] ??= ['name' => $name, 'sort' => $rs->section->sort_order ?? 99, 'results' => []];
                $rows[$name]['results'][] = $res;
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $results = collect($row['results']);
            $worst = $results->sortBy(fn ($r) => $rank[$r['tone']] ?? 9)->first();
            if ($multi && $results->count() > 1) {
                $failed = $results->where('tone', 'fail')->count();
                $passed = $results->where('tone', 'pass')->count();
                $detail = $failed > 0
                    ? $failed.' of '.$results->count().' articles with issues'
                    : ($passed > 0 ? $passed.' of '.$results->count().' articles passed' : ($worst['detail'] ?: ''));
            } else {
                $detail = $worst['detail'];
            }
            $out[] = [
                'name' => $row['name'], 'sort' => $row['sort'], 'tone' => $worst['tone'], 'label' => $worst['label'], 'detail' => $detail,
                'recorded' => $results->contains(fn ($r) => $r['label'] !== 'NOT RECORDED'),
            ];
        }

        usort($out, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $out;
    }

    // ── Photos ───────────────────────────────────────────────────────────────

    /**
     * The section's photos, grouped by the row they belong to (checkpoint, carton type,
     * production stage, …) in the order those rows print. Photos of N/A rows are left out;
     * defect-row photos are printed in the defect table instead. Any other photo (a task_key
     * mismatch) is still printed, in an unlabelled group.
     *
     * @return array<int, array{label: ?string, images: Collection}>
     */
    public function photoGroups(InspectionRunSection $rs): array
    {
        $images = $rs->attachments->filter(fn ($a) => $a->isImage())->values();
        $data = $rs->data ?? [];
        $type = $rs->section->section_type;
        $slug = $rs->section->slug;
        $groups = [];
        $used = [];

        $byTask = fn (string $key) => $images->where('task_key', $key)->values();
        $take = function (Collection $imgs, ?string $label) use (&$groups, &$used) {
            foreach ($imgs as $img) {
                $used[$img->id] = true;
            }
            if ($imgs->isEmpty()) {
                return;
            }
            $k = $label ?? '';
            $groups[$k] ??= ['label' => $label ?: null, 'images' => collect()];
            $groups[$k]['images'] = $groups[$k]['images']->concat($imgs);
        };
        $skip = function (Collection $imgs) use (&$used) {
            foreach ($imgs as $img) {
                $used[$img->id] = true;
            }
        };

        switch (true) {
            case $type === 'defects':
                foreach ($rs->defects as $row) {
                    $skip($byTask('defect_row_'.$row->id));
                }
                break;
            case $slug === 'barcode_testing':
                $take($byTask('barcode_photos'), null);
                break;
            case $slug === 'variations_techpack':
                $take($byTask('techpack_photos'), null);
                break;
            case $slug === 'carton_dimensions_weight':
                foreach ($data['cartons'] ?? [] as $ci => $carton) {
                    $take($byTask('carton_dim_'.$ci), ($carton['carton_type'] ?? '') ?: 'Carton '.($ci + 1));
                }
                break;
            case $type === 'checklist':
                foreach ($data['items'] ?? [] as $idx => $item) {
                    self::resultKey($item['result'] ?? '') === 'na'
                        ? $skip($byTask('checkpoint_'.$idx))
                        : $take($byTask('checkpoint_'.$idx), $item['label'] ?? null);
                }
                break;
            case $type === 'checkpoint' || $type === 'task_list':
                foreach ($this->taskResults($rs) as $t) {
                    self::resultKey($t['result']) === 'na' ? $skip($byTask($t['key'])) : $take($byTask($t['key']), $t['label']);
                }
                break;
            case $type === 'production_stages':
                foreach ($data['selections'] ?? [] as $ps) {
                    if (! empty($ps['stage'])) {
                        $key = 'ps_'.preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $ps['stage'])));
                        $take($byTask($key), $ps['stage']);
                    }
                }
                break;
            case $type === 'container':
                $take($byTask('container_photos'), null);
                break;
            case $type === 'verification':
                $take($byTask('verification_photos'), null);
                break;
            case $slug === 'overall_carton_condition':
                $take($byTask('carton_condition'), null);
                break;
        }

        $take($images->reject(fn ($img) => isset($used[$img->id]))->values(), null);

        return array_values($groups);
    }

    /** Photos of one defect row (printed as thumbnails in its table row). */
    public function defectRowPhotos(InspectionRunSection $rs, $row): Collection
    {
        return $rs->attachments->filter(fn ($a) => $a->isImage() && $a->task_key === 'defect_row_'.$row->id)->values();
    }

    /**
     * The photo that represents a run: its cover photo, else the first product photo.
     */
    public function coverImage(InspectionRun $run)
    {
        foreach (['cover_photo', 'images'] as $type) {
            $rs = $run->runSections->first(fn ($x) => $x->section?->section_type === $type
                && $x->status !== 'na'
                && $x->attachments->contains(fn ($a) => $a->isImage()));
            if ($rs) {
                return $rs->attachments->first(fn ($a) => $a->isImage());
            }
        }

        return null;
    }

    // ── Labels ───────────────────────────────────────────────────────────────

    public function articleLabel(InspectionRun $run): string
    {
        $parts = array_filter([
            $run->sample?->sample_code,
            $run->sample?->product_name,
        ]);
        $label = $parts ? implode(' — ', $parts) : 'Run #'.$run->run_number;

        return $run->sampleColor ? $label.' · '.$run->sampleColor->name : $label;
    }

    /** Distinct non-empty values, comma separated, or a dash. */
    public static function join(iterable $values): string
    {
        return collect($values)->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => $v !== null && $v !== '')->unique()->implode(', ') ?: '—';
    }

    /** Values of one General Information field across runs. */
    public function generalInfo(Collection $runs, string $field): string
    {
        return self::join($runs->flatMap(fn ($r) => $r->runSections
            ->filter(fn ($x) => $x->section?->section_type === 'general_info')
            ->map(fn ($x) => $x->data[$field] ?? null)));
    }
}
