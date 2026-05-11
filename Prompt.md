I am building a Laravel ERP project called TradeSyncERP.

Act as a senior Laravel + UI architect.

🧠 Project Context

I already have:

Laravel project created
Spatie Activity Log installed (audit logging)
Project.md file in root containing full business requirements
Sidebar + Header already implemented based on reference UI
Static HTML templates located in:
resources/views/template/

These templates contain full UI and must be converted into Laravel Blade views.

🎯 TASK

Convert all static HTML templates into a clean, module-based Laravel Blade structure using my existing base layout.

🧱 BLADE STRUCTURE (IMPORTANT — KEEP SIMPLE)

Use ONLY this structure:

resources/views/

├── index.blade.php   (MASTER LAYOUT - already exists with sidebar + header + footer)

├── invoices/
├── proposals/
├── customers/
├── payments/
├── projects/
├── employees/
├── vendors/
├── settings/

├── components/ (optional only if needed)
⚙️ RULES
1. Base Layout
Use existing index.blade.php as master layout
All views must:
@extends('index')
use @section('content')
2. Convert HTML → Blade

All source templates are located in:

resources/views/template/

You must:

extract UI from these files
convert them into Blade views
DO NOT redesign UI
only convert structure to Blade
📌 FILE MAPPING
📄 Forms

Convert:

invoice-create.html
proposal-edit.html

To:

invoices/create.blade.php
proposals/edit.blade.php
📊 Tables (Sortable / Editable)

Convert:

payment.html

To:

payments/index.blade.php

Must include:

sortable structure
pagination-ready layout
action buttons (edit/delete/view)
Spatie permission-ready UI
🧾 Tabbed Views

Convert:

proposal-view.html

To:

proposals/show.blade.php

Must include:

tab navigation
modular sections
👁 Detail Views

Convert:

invoice-view.html
proposal-view.html
customers-view.html

To:

invoices/show.blade.php
proposals/show.blade.php
customers/show.blade.php
👥 Customers / Employees / Vendors

Use:

customers-view.html

Apply to:

customers
employees
vendors

Structure:

customers/
employees/
vendors/
📋 Editable Tables

Convert:

customers.html

To:

customers/index.blade.php

Must include:

inline editable fields
dropdown actions
Spatie permission-based UI (show/hide buttons)
🖼 Projects Table (with images)

Convert:

projects.html

To:

projects/index.blade.php

Must include:

image preview in table
responsive layout
⚙ Settings Module

Convert:

settings-general.html

To:

settings/general.blade.php

Must include:

Company Profile
Roles & Permissions (Spatie UI)
Currency settings
System settings

Also:

nested sidebar inside settings page
tab-based navigation
🔐 SPATIE PERMISSION REQUIREMENT

All UI must support:

roles
permissions
conditional UI rendering

Example:

Only users with permission see delete buttons
Admin-only actions hidden via Blade conditionals
🎨 DESIGN RULES
DO NOT redesign UI
Preserve original HTML design
Only convert to Blade syntax
Use:
@extends
@section
@include only if necessary
Keep code clean and modular
Avoid duplication
📦 OUTPUT EXPECTED

Generate:

Blade file structure
Converted views
Clean layout usage
Module-based ERP structure
Spatie-ready UI
🚀 GOAL

Convert static templates into a production-ready Laravel ERP frontend architecture using:

Module-based structure (no pages folder)
Spatie roles & permissions
Laravel best practices
scalable ERP design