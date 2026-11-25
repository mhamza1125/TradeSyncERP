/*
 * AQL plan lookups for the browser previews (run AQL section, AQL calculator tool).
 *
 * This file holds NO sampling tables. All numbers come from the server,
 * App\Services\Inspection\AqlCalculationService::tableForJs(), which already resolves
 * the arrows of Table II-A. The server recalculates everything on save; this is a preview.
 *
 * Usage: const aql = AqlPlan(@json($aqlJsData));
 */
window.AqlPlan = function (data) {
    'use strict';

    // Canonical AQL key ('0.1' -> '0.10', '4' -> '4.0'), compared in integer thousandths.
    function key(value) {
        if (value === 'not_allowed') return value;
        const t = Math.round(parseFloat(value) * 1000);
        if (!Number.isFinite(t)) return null;
        return data.aqls.find(k => Math.round(parseFloat(k) * 1000) === t) || null;
    }

    function lotRow(lot) {
        return data.lotSizeTable.find(r => lot >= r.min && (r.max === null || lot <= r.max)) || null;
    }

    function codeLetter(lot, level) {
        const row = lotRow(lot);
        return row ? (row.codes[level] || null) : null;
    }

    // Sample size of the base code letter, capped at the lot (100% inspection).
    function baseSample(lot, level) {
        const code = codeLetter(lot, level);
        return code ? Math.min(data.sampleSizes[code], lot) : null;
    }

    // Plan for one severity: { ac, re, ss (capped at lot), code } or null.
    function plan(aqlValue, lot, level) {
        const code = codeLetter(lot, level);
        const k = key(aqlValue);
        if (!code || !k) return null;
        if (k === 'not_allowed') return { ac: 0, re: 1, ss: Math.min(data.sampleSizes[code], lot), code: code };
        const p = data.plans[code][k];
        return { ac: p.ac, re: p.re, ss: Math.min(p.n, lot), code: p.code };
    }

    // Units to inspect so every plan in use can be applied (same rule as the server).
    function sampleSize(lot, level, aqlValues) {
        const base = baseSample(lot, level);
        if (base === null) return null;
        return aqlValues.reduce((max, v) => Math.max(max, plan(v, lot, level)?.ss || 0), base);
    }

    return { key, codeLetter, baseSample, plan, sampleSize, sampleSizes: data.sampleSizes, lotSizeTable: data.lotSizeTable };
};
