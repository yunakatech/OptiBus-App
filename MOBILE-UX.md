# Mobile layout and navigation

## Loading and diagnostics

`config/ui.php` owns the operational deferred policy. An unset, empty, or invalid
`OPS_INERTIA_DEFERRED` follows the hosting default: enabled outside Vercel, disabled
on Vercel. Explicit true/false overrides apply on either host. Tests keep deferred
enabled; the pure resolver has separate tests for the production decision matrix.
Benchmark both settings on Vercel before changing its default, since deferral adds
HTTP round trips.

Set `NAVIGATION_METRICS=true` to include `Server-Timing: app;dur=…` on web responses.
This measures Laravel middleware/controller/serialization processing, excluding
serverless startup and part of framework boot. No SQL or customer data is emitted.

Client diagnostics are enabled in development. To enable for one production tab:

```js
sessionStorage.setItem('optibus.navigationMetrics', '1');
location.reload();
```

Then navigate normally, revisit the same menus, and inspect:

```js
console.table(window.optibusNavigationMetrics);
```

Alternatively build with `VITE_NAVIGATION_METRICS=true`. Samples stay in memory,
are capped at 40, and contain component names rather than URLs, IDs, query strings,
or customer data. Nothing is transmitted to an analytics endpoint.

| Field                             | Interpretation                                                                              |
| --------------------------------- | ------------------------------------------------------------------------------------------- |
| `kind`, `visit`                   | Document/Inertia; first/repeat component visit in this document                             |
| `requestTtfbMs`                   | Request start to first response byte, including network/startup                             |
| `serverAppMs`                     | Optional `Server-Timing` app duration                                                       |
| `responseMs`                      | First to last response byte                                                                 |
| `moduleResolveMs`                 | Wait for the page module, including loading/evaluation/dependencies                         |
| `jsTransferBytes`                 | Browser-reported JS transfer size; zero can mean cache or unavailable timing                |
| `jsResourceCount`, `jsDownloadMs` | Observed JS resources and sum of response download durations; parallel times overlap        |
| `renderAfterModuleMs`             | Module resolution to the next two animation frames; a paint opportunity proxy, not CPU time |
| `nextPaintMs`, `dataReadyMs`      | Navigation start to paint opportunity/main page data available                              |

Use the browser Network and Performance recordings alongside these samples to
identify parsing, long tasks, paint, redirects, and duplicate requests. Compare
the same device, viewport, account/pool, network profile, and data volume. Collect
at least five first visits with a fresh context and five repeat visits; report
median and worst observed values. Deferred panels can finish after main data is
ready. Public/auth pages report their initial content rather than an API dataset.

## Data contracts

- `/bookings` accepts `from`, `to`, `route`, `payment`, `page`, `per_page`. Dates
  default to today; reversed dates are normalized; page size is 1–100 (default 24).
- `bookingPagination` and `bookingListSummary` describe the filtered result.
  `bookingGroups` contains one page of complete trip summaries, with
  `detail_loaded=false`, `bookings=[]`, `paid_amount`, and `unpaid_amount`.
- Trip metadata is aggregated in SQL before application normalization/pagination.
  This preserves route aliases and empty assignments without fetching all
  passengers. The aggregate identity set is still processed on the server;
  extremely wide historical periods should be profiled separately.
- `/api/bookings/groups/{groupKey}` requires `booking.view` and the existing
  tenant/pool scope. It returns `{success, group}` with complete passenger history.
  Unknown/inaccessible groups return 404. Direct detail URLs and print/PDF paths
  use the same scoped group lookup. The old 2,000-passenger cutoff is removed.
- Copy operations load full group details on demand. Returning from detail keeps
  the list's filters and requested page. Filter changes reset to page one.
- Dashboard deferred requests are grouped into trends, operations, activity,
  and performance. Each panel has its own loading/error/retry boundary. Chart
  code loads near the viewport. Carter/bagasi form/detail pages load masters
  without also querying the transaction list.

## Shared mobile behavior

- Use `.mobile-page` on page roots; retain one owner for navbar safe-area spacing.
  The tenant/pool selectors share one row. Avoid padding another navbar height
  inside a page; reserve extra space only for a real page-specific action bar.
- `ViewportOnly` renders the appropriate responsive subtree, including list
  cards/table variants. State that must survive a breakpoint change belongs in
  the parent. Do not put unsaved state inside disposable presentation children.
- `ResponsiveFilterBar` supports a visible mobile search and secondary filter
  panel. Draft apply/reset/cancel handlers remain page-owned. The footer stays
  in the viewport-sized sheet layout rather than using a second fixed offset.
- Use visible labels, 16px editable input text, effective touch targets of at
  least 44px, and readable supporting text. Forms retain field labels when filled.
  Native date controls are used on phones; desktop calendars retain reactive bounds.
- Use Cari, Simpan, Sebelumnya, Berikutnya, Pelanggan, and Perusahaan for public UI
  labels. Request keys, status values, prices, permission names and destinations
  are not translated. Pagination groups use `.mobile-pagination` to wrap safely.
- Restrict horizontal scrolling to explicit table/carousel containers. Do not
  hide overflow globally or split every label with `overflow-wrap:anywhere`.

## Verification and reproduction

```powershell
$env:MOBILE_PREVIEW_OUTPUT="$PWD/storage/framework/testing/mobile-preview"
php vendor/phpunit/phpunit/phpunit --filter MobilePagesTest
npm run build
npm run check:mobile-layout
npm run guard:mobile-labels
npm run guard:navigation
npm run types:check
git diff --check
```

The browser check uses local synthetic Inertia fixtures from SQLite in memory,
Chrome headless (`CHROME_PATH` can override the executable), and the production
build. It checks 41 public, auth, tenant operational/settings, and platform pages
at 320/360/390/430px and a 1440px desktop regression pass, plus filter footer visibility under viewport shrink,
detail/back navigation, a failed request and delayed retry, and independent
dashboard failure/retry. Screenshots and JSON reports stay under the ignored
`storage/framework/testing/mobile-preview` directory. The label guard covers
pages and operational feature components; it is a static guard, not a complete
accessibility audit.

The forgot/reset password screens intentionally redirect to login in this repo;
the smoke test preserves that behavior. Onboarding uses a separate synthetic
non-superadmin account. Platform fixtures include the context without a tenant.
Existing permission/pool tests provide additional authorization coverage.

The fixture check cannot measure production database latency, serverless cold
starts, real mobile keyboard behavior, Safari/WebView differences, or live
transaction integrations. Check these on a physical device and staging with the
same navigation protocol before making production performance claims.

Local build comparison (uncompressed, existing October 30 artifact versus this
implementation): dashboard chunk 254,397 → approximately 22,100 bytes. Chart.js
is now a separate approximately 202KB chunk; it is not removed from total code.
Other page chunks gained labels/responsive guards. These are bundle measurements,
not evidence that production TTFB or interaction latency improved by a percentage.

Local verification completed: 28 focused PHP tests with 721 assertions; 205
browser layout checks (164 mobile and 41 desktop), no document overflow or runtime
exceptions; detail/back, failed-request retry, delayed response, dashboard panel
failure/retry, and filter footer visibility passed. Type checking, internal
navigation guard, visible field label guard, production build and diff whitespace
checks passed. Physical-device and production-latency checks remain external to
this synthetic fixture verification.

An exploratory run of the complete `DashboardTest` also reported two failures
outside the focused cases: its general authenticated-page smoke test expects
the Booking Console without establishing a pool, and its legacy URL test expects
a redirect for a removed route. These were not counted among the 28 passing
focused tests; the full repository suite has not been declared green.
