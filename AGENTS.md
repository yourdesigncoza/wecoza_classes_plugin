# Repository Guidelines

## Project Structure & Module Organization
The plugin boots from `wecoza-classes-plugin.php`, which loads `app/bootstrap.php` and wires the MVC autoloader. Core logic lives in `app/`: Controllers own WordPress hooks and AJAX, Models wrap PostgreSQL access, Views are `.view.php` templates, Services expose shared utilities, and Helpers keep UI helpers isolated. Shared configuration and feature registration reside in `config/app.php`, static assets ship from `assets/`, and long-form documentation for agents is organized under `docs/` with schema references in `schema/`.

## Build, Test, and Development Commands
- `wp plugin activate wecoza-classes-plugin` — enable the plugin inside your target WordPress install.
- `wp eval "echo (new WeCozaClasses\\Services\\Database\\DatabaseService())->testConnection();"` — confirm PostgreSQL credentials after updating the `wecoza_postgres_*` options.
- `psql -f schema/classes_schema.sql -h <host> -p <port> -U <user> <database>` — replay schema changes against the managed cluster before deploying.
- `wp eval "WeCozaClasses\\init();"` — force-load controllers during CLI experiments when WordPress has not fully bootstrapped.

## Coding Style & Naming Conventions
Write PHP for 7.4+ using 4-space indentation, strict types where practical, and PSR-12 structure while honouring WordPress escaping and nonce patterns. Namespaces stay under `WeCozaClasses\`; class files mirror directory names (e.g., `Controllers/ClassController.php`). Procedural hooks must use the `wecoza_classes_` prefix, and view templates should emit escaped markup with Bootstrap 5 utility classes from `design-guide.md`. JavaScript modules in `assets/js/` favour modular IIFEs, jQuery helpers, and camelCase function names.

## Testing Guidelines
The repository ships without an automated suite, so lean on targeted manual checks. Exercise the primary shortcodes (`wecoza_capture_class`, `wecoza_display_classes`, `qa_analytics_dashboard`) inside a staging site, verify AJAX endpoints through the browser console or `wp ajax` tooling, and confirm database writes via readbacks in the admin UI. Document scenarios and edge cases in `daily-updates/` or the relevant `docs/` tier so future agents inherit context.

## Commit & Pull Request Guidelines
Commits follow a concise, imperative summary (`Update class management UI and helper functions`); keep them scoped and reference affected components in the body when needed. Pull requests should describe the problem, outline schema or configuration impacts, list manual tests run, and attach screenshots for UI or analytics updates. Link Jira/GitHub issues when available and note any documentation or SQL artefacts that must accompany the change.

## Security & Configuration Tips
Never hard-code database credentials—set them via WordPress options or environment files outside the repo. Audit changes for nonce validation and capability checks before exposing new AJAX handlers. When exporting data, respect the allowed file types in `config/app.php` and review uploads for size limits and sanitation.
