Create a Laravel migration and an Eloquent model for a `companies` table to store the application owner's business details for use in report headers, footers, and invoices.

1. **Migration**: Create a migration file `database/migrations/YYYY_MM_DD_000000_create_companies_table.php` with the following schema:
    - `id()`
    - `name` (string)
    - `email` (string)
    - `phone` (string)
    - `address` (text)
    - `city`, `state`, `zip`, `country` (strings)
    - `website` (string, nullable)
    - `tax_registration_number` (string, nullable) — for tax/legal compliance on reports
    - `timestamps()`
    - `softDeletes()`

2. **Model**: Create `app/Models/Company.php` ensuring it:
    - Uses `Illuminate\Database\Eloquent\SoftDeletes`.
    - Uses `Spatie\Activitylog\Traits\LogsActivity` and implements `getActivitylogOptions()` following the project's existing pattern (log fillable, only dirty, and skip empty logs).
    - Includes a `$fillable` array for all fields.
    - Defines a polymorphic relationship to the `attachments` table (using the `attachable` morph) to handle the company logo, consistent with `database/migrations/2026_05_12_000001_create_attachments_table.php`.