# Repository Guidelines

## Project Structure & Module Organization

This is a Laravel 13 + Inertia/Svelte application. Backend code lives in `app/`, with HTTP controllers in `app/Http/Controllers`, middleware in `app/Http/Middleware`, services in `app/Services`, and shared helpers in `app/Support`. Routes are in `routes/web.php` and `routes/settings.php`. Database migrations, factories, and seeders are under `database/`. Svelte pages and components live in `resources/js/`; styles live in `resources/css/`. Public assets are in `public/`, docs in `docs/`, and automated tests in `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands

Use Composer and npm scripts from the repository root.

```bash
composer dev
```
Runs Laravel server, queue listener, logs, and Vite together.

```bash
npm run dev
npm run build
npm run types:check
npm run guard:navigation
```
Starts Vite, builds production assets, checks Svelte/TypeScript, and validates internal navigation.

```bash
php artisan test
composer test
composer ci:check
```
Runs PHPUnit directly, or the project test/CI pipeline with Pint and frontend checks.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, 4-space indentation, final newline. PHP uses Laravel Pint with the `laravel` preset. Svelte/TypeScript uses Prettier with single quotes, semicolons, 80-column print width, Tailwind class sorting, and 4-space tabs. Use PascalCase for Svelte pages/components, camelCase for TypeScript variables/functions, and descriptive Laravel class names such as `TenantProvisioningService`.

## Testing Guidelines

Tests use PHPUnit. Place feature tests in `tests/Feature` and unit tests in `tests/Unit`. Name test classes after the behavior or area, for example `RegistrationTest`, `SubscriptionTest`, or `FeatureGateTest`. Prefer focused filters during development:

```bash
php artisan test --filter=Subscription
```

Add tests for tenant scoping, billing lifecycle, permissions, and user-visible workflow changes.

## Commit & Pull Request Guidelines

Recent commits use concise imperative subjects, for example `Improve SaaS tenant billing lifecycle` and `Improve departure data desktop UX`. Keep commits scoped. Before opening a PR, include a short summary, test commands run, screenshots for UI changes, migration notes, and any production commands such as `php artisan migrate --force`.

## Security & Configuration Tips

Never commit `.env`, secrets, payment credentials, or uploaded proofs. Use `.env.example` for documented config. For SaaS enforcement, production should set `SAAS_FEATURE_GATING_ENABLED=true` and run migrations before deploy.
