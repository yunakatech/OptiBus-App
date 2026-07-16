# Laravel + Svelte Starter Kit

## Introduction

Our Svelte starter kit provides a robust, modern starting point for building Laravel applications with a Svelte frontend using [Inertia](https://inertiajs.com).

Inertia allows you to build modern, single-page Svelte applications using classic server-side routing and controllers. This lets you enjoy the frontend power of Svelte combined with the incredible backend productivity of Laravel and lightning-fast Vite compilation.

This Svelte starter kit utilizes Svelte 5, TypeScript, Tailwind, and the [shadcn-svelte](https://shadcn-svelte.com) and [bits-ui](https://bits-ui.com) component libraries.

## Official Documentation

Documentation for all Laravel starter kits can be found on the [Laravel website](https://laravel.com/docs/starter-kits).

## Deployment Guides

- Coolify: [docs/coolify-deploy.md](docs/coolify-deploy.md)
- Render + Neon: [docs/render-neon-deploy.md](docs/render-neon-deploy.md)
- Cloudflare migration: [docs/cloudflare-deploy.md](docs/cloudflare-deploy.md)

### Vercel Notes

- Keep Vercel project secrets in the Vercel dashboard, not in `vercel.json`.
- Use [vercel.env.example](vercel.env.example) as the baseline for required project environment variables.
- Before removing any existing secret from `vercel.json`, make sure the matching value already exists in Vercel Project Settings -> Environment Variables.

## Legacy Data Import (Booking Core)

Project ini punya command import untuk menarik data inti booking dari aplikasi legacy (`routes`, `units`, `schedules`, `customers`, `segments`, `bookings`).

1. Atur koneksi legacy di `.env`:

```env
LEGACY_DB_CONNECTION=pgsql
LEGACY_DB_HOST=127.0.0.1
LEGACY_DB_PORT=5432
LEGACY_DB_DATABASE=cabomultibus_db
LEGACY_DB_USERNAME=postgres
LEGACY_DB_PASSWORD=
LEGACY_DB_SCHEMA=public
LEGACY_DB_SSLMODE=prefer
```

2. Jalankan dry-run:

```bash
php artisan legacy:import-booking-core --dry-run
```

3. Jalankan import aktual:

```bash
php artisan legacy:import-booking-core --truncate --chunk=1000
```

4. Import data operasi (driver/charter/luggage/cancellation):

```bash
php artisan legacy:import-operations --truncate --chunk=1000
```

## Booking API (Session Auth)

Endpoint ini ada di `routes/web.php` dan dilindungi middleware `auth` + `verified`.

UI:
- `/bookings` (halaman booking + monitoring)
- `/booking-console` (mode kasir / live console tanpa sidebar)

- `GET /api/bookings/routes-by-date?tanggal=YYYY-MM-DD`
- `GET /api/bookings/schedules?rute=...&tanggal=YYYY-MM-DD`
- `GET /api/bookings/seats-detail?rute=...&tanggal=YYYY-MM-DD&jam=HH:MM&unit=1`
- `POST /api/bookings/submit`
- `POST /api/bookings/update`
- `POST /api/bookings/cancel`

## Master + Operations API (Session Auth)

- `GET /api/master/charter-routes`
- `GET /api/master/segments?route_name=...`
- `GET /api/master/segment-price?id=...`
- `GET /api/master/units`
- `GET /api/master/drivers`
- `GET /api/master/luggage-services`
- `GET /api/master/customers/search?q=...`
- `POST /api/ops/charters`
- `POST /api/ops/luggages`
- `POST /api/ops/luggages/raw`
- `GET/POST/DELETE /api/admin/customer-bagasi`
- `GET/POST/DELETE /api/admin/customer-charter`
- `POST /api/admin/luggages/raw`
- `POST /api/admin/charters/{id}/mark-bop-done`
- `POST /api/admin/charters/{id}/mark-paid`
- `POST /api/admin/luggages/{id}/mark-paid`
- `POST /api/admin/luggages/{id}/mark-active`
- `POST /api/admin/luggages/{id}/mark-done`
- `POST /api/admin/luggages/{id}/mark-canceled`
- `GET /api/admin/luggages/{id}/tracking`
- `POST /api/admin/luggages/{id}/tracking`
- `POST /api/admin/assignments/conflicts`
- `GET /api/admin/reports/revenue-csv?from=YYYY-MM-DD&to=YYYY-MM-DD&type=reguler|bagasi|charter`

## Contributing

Thank you for considering contributing to our starter kit! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

All contributions to the Starter Kits from now on should be made through [Maestro](https://github.com/laravel/maestro).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## License

The Laravel + Svelte starter kit is open-sourced software licensed under the MIT license.
