Fix the `syntax error, unexpected end of file` occurring in `resources/views/finance/salary/show.blade.php` at line 445. The error is triggered by an unclosed Blade directive (likely an `@if`, `@can`, or `@foreach`) located before the final `@endpush` tag. 

Specifically:
1. Audit the `@push('modals')` and `@push('scripts')` sections in `show.blade.php` to ensure all conditional and loop directives are properly closed.
2. Perform a similar syntax audit on `resources/views/finance/salary/edit.blade.php` and the partial `resources/views/finance/salary/_form.blade.php` to prevent similar regressions.
3. Ensure that the "Mark as Paid" modal logic and the "Salary Lines" update form are correctly nested within their respective permission checks and status conditionals.
4. Verify that the page loads correctly at `http://127.0.0.1:8000/salary/{id}` once the directives are balanced.