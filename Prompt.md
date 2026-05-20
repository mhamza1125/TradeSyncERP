Please refactor the views for the following master modules to remove the secondary sidebar layout: **Categories**, **Testing Parameters**, **Currencies**, and **Expense Heads**.

Specifically, perform these actions in the `index.blade.php`, `create.blade.php`, and `edit.blade.php` files within each of these directories:
1. Remove the `@include('partials.masters-sidebar')` directive.
2. Remove the `<div class="main-content d-flex">` wrapper (and its corresponding closing `</div>`) that currently surrounds the `@include` and the `.content-area` div.
3. Ensure the `.content-area` div remains as the primary content container so the layout reverts to the standard full-width view used elsewhere in the application.

Finally, delete the now-obsolete partial file: `resources/views/partials/masters-sidebar.blade.php`.