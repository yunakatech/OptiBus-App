<?php

use App\Http\Controllers\AdminOpsController;
use App\Http\Controllers\AdminOpsFlowsController;
use App\Http\Controllers\AdminOpsMasterController;
use App\Http\Controllers\Api\AdminOpsApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\OperationsApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CharterDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LuggageDocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlatformDashboardController;
use App\Http\Controllers\StaticAssetController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::get('style.css', [StaticAssetController::class, 'style'])->name('style.css');

// Platform Admin Dashboard (SaaS metrics — super admin only)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('platform/dashboard', PlatformDashboardController::class)
        ->middleware('permission:pool.manage')
        ->name('platform.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::inertia('menu', 'Menu')->name('menu.index');
    Route::get('bookings', BookingController::class)->middleware('permission:booking.view')->name('bookings.index');
    Route::get('bookings/detail/{groupKey}', BookingController::class)->middleware('permission:booking.view')->name('bookings.detail');
    Route::get('bookings/manifest/{groupKey}/print', [BookingController::class, 'printManifest'])->middleware('permission:booking.print')->middleware('feature:booking.manifest')->name('bookings.manifest.print');
    Route::get('bookings/manifest/{groupKey}/pdf', [BookingController::class, 'downloadManifestPdf'])->middleware('permission:booking.print')->middleware('feature:booking.manifest')->name('bookings.manifest.pdf');
    Route::get('bookings/ticket/{bookingId}/print', [BookingController::class, 'printTicket'])->middleware('permission:booking.print')->middleware('feature:booking.ticket_print')->name('bookings.ticket.print');
    Route::get('bookings/ticket/{bookingId}/pdf', [BookingController::class, 'downloadTicketPdf'])->middleware('permission:booking.print')->middleware('feature:booking.ticket_print')->name('bookings.ticket.pdf');
    Route::get('booking-console', BookingController::class)->middleware('permission:booking.view')->name('booking-console.index');
    Route::get('payments', PaymentController::class)->middleware('permission:payment.update,booking.update,charter.update,luggage.update')->name('payments.index');
    Route::get('payments/export', [PaymentController::class, 'export'])->middleware('permission:payment.update,booking.update,charter.update,luggage.update')->middleware('feature:report.export_csv')->name('payments.export');
    Route::get('charters', AdminOpsFlowsController::class)->middleware('permission:charter.view')->defaults('tab', 'charters')->defaults('locked', true)->name('charters.index');
    Route::get('charters/form', AdminOpsFlowsController::class)->middleware('permission:charter.create')->defaults('tab', 'charters')->defaults('mode', 'form')->defaults('locked', true)->name('charters.form');
    Route::get('charters/view/{id}', AdminOpsFlowsController::class)->middleware('permission:charter.view')->defaults('tab', 'charters')->defaults('mode', 'view')->defaults('locked', true)->name('charters.view');
    Route::get('charters/{id}/invoice/print', [CharterDocumentController::class, 'print'])->middleware('permission:charter.print')->middleware('feature:charter.invoice_print')->name('charters.invoice.print');
    Route::get('charters/{id}/invoice/pdf', [CharterDocumentController::class, 'pdf'])->middleware('permission:charter.print')->middleware('feature:charter.invoice_print')->name('charters.invoice.pdf');
    Route::get('luggages', AdminOpsFlowsController::class)->middleware('permission:luggage.view')->defaults('tab', 'luggages')->defaults('locked', true)->name('luggages.index');
    Route::get('luggages/form', AdminOpsFlowsController::class)->middleware('permission:luggage.create')->defaults('tab', 'luggages')->defaults('mode', 'form')->defaults('locked', true)->name('luggages.form');
    Route::get('luggages/{id}/print', [LuggageDocumentController::class, 'print'])->middleware('permission:luggage.print')->middleware('feature:luggage.resi_print')->name('luggages.print');
    Route::get('luggages/{id}/pdf', [LuggageDocumentController::class, 'pdf'])->middleware('permission:luggage.print')->middleware('feature:luggage.resi_print')->name('luggages.pdf');
    Route::get('report', AdminOpsController::class)->middleware('permission:report.view')->defaults('tab', 'reports')->defaults('locked', true)->name('report.index');
    Route::get('reports', AdminOpsController::class)->middleware('permission:report.view')->defaults('tab', 'reports')->defaults('locked', true)->name('reports.index');
    Route::get('admin-ops', AdminOpsController::class)->middleware('permission:master.view,driver.view,armada.view,user.manage,pool.manage,logs.view,report.view')->name('admin-ops.index');
    Route::get('admin-ops/routes', AdminOpsController::class)->middleware('permission:master.view')->defaults('tab', 'routes')->defaults('locked', true)->name('admin-ops.routes');
    Route::get('admin-ops/schedules', AdminOpsController::class)->middleware('permission:master.view')->defaults('tab', 'schedules')->defaults('locked', true)->name('admin-ops.schedules');
    Route::get('admin-ops/drivers', AdminOpsController::class)->middleware('permission:driver.view')->defaults('tab', 'drivers')->defaults('locked', true)->name('admin-ops.drivers');
    Route::get('admin-ops/services', AdminOpsController::class)->middleware('permission:master.view')->defaults('tab', 'services')->defaults('locked', true)->name('admin-ops.services');
    Route::get('admin-ops/segments', AdminOpsController::class)->middleware('permission:master.view')->defaults('tab', 'segments')->defaults('locked', true)->name('admin-ops.segments');
    Route::get('admin-ops/customers', AdminOpsController::class)->middleware('permission:customer.view')->defaults('tab', 'customers')->defaults('locked', true)->name('admin-ops.customers');
    Route::get('admin-ops/units', AdminOpsController::class)->middleware('permission:master.view')->defaults('tab', 'units')->defaults('locked', true)->name('admin-ops.units');
    Route::get('admin-ops/units/layout/{id}', AdminOpsController::class)->middleware('permission:master.manage')->defaults('tab', 'units')->defaults('mode', 'layout')->defaults('locked', true)->name('admin-ops.units.layout');
    Route::get('admin-ops/armadas', AdminOpsController::class)->middleware('permission:armada.view')->defaults('tab', 'armadas')->defaults('locked', true)->name('admin-ops.armadas');
    Route::get('admin-ops/armadas/view/{id}', AdminOpsController::class)->middleware('permission:armada.view')->defaults('tab', 'armadas')->defaults('mode', 'view')->defaults('locked', true)->name('admin-ops.armadas.view');
    Route::get('admin-ops/pools', AdminOpsController::class)->middleware('permission:pool.manage')->defaults('tab', 'pools')->defaults('locked', true)->name('admin-ops.pools');
    Route::get('admin-ops/users', AdminOpsController::class)->middleware('permission:user.manage')->defaults('tab', 'users')->defaults('locked', true)->name('admin-ops.users');
    Route::get('admin-ops/roles', AdminOpsController::class)->middleware('permission:role.manage')->defaults('tab', 'roles')->defaults('locked', true)->name('admin-ops.roles');
    Route::get('admin-ops/cancellations', AdminOpsController::class)->middleware('permission:logs.view')->defaults('tab', 'cancellations')->defaults('locked', true)->name('admin-ops.cancellations');
    Route::get('admin-ops/reports', AdminOpsController::class)->middleware('permission:report.view')->defaults('tab', 'reports')->defaults('locked', true)->name('admin-ops.reports');

    Route::get('admin-ops/flows', AdminOpsFlowsController::class)->middleware('permission:booking.view,charter.view,luggage.view,report.view')->name('admin-ops.flows');
    Route::get('admin-ops/flows/charters', AdminOpsFlowsController::class)->middleware('permission:charter.view')->defaults('tab', 'charters')->defaults('locked', true)->name('admin-ops.flows.charters');
    Route::get('admin-ops/flows/luggages', AdminOpsFlowsController::class)->middleware('permission:luggage.view')->defaults('tab', 'luggages')->defaults('locked', true)->name('admin-ops.flows.luggages');
    Route::get('admin-ops/flows/assignments', AdminOpsFlowsController::class)->middleware('permission:booking.view')->defaults('tab', 'assignments')->defaults('locked', true)->name('admin-ops.flows.assignments');
    Route::get('admin-ops/flows/export', AdminOpsFlowsController::class)->middleware('permission:report.view')->defaults('tab', 'export')->defaults('locked', true)->name('admin-ops.flows.export');

    Route::get('admin-ops/master', AdminOpsMasterController::class)->middleware('permission:customer.view,master.view')->name('admin-ops.master');
    Route::get('admin-ops/master/customer-bagasi', AdminOpsMasterController::class)->middleware('permission:customer.view')->defaults('tab', 'customer-bagasi')->defaults('locked', true)->name('admin-ops.master.customer-bagasi');
    Route::get('admin-ops/master/customer-charter', AdminOpsMasterController::class)->middleware('permission:customer.view')->defaults('tab', 'customer-charter')->defaults('locked', true)->name('admin-ops.master.customer-charter');
    Route::get('admin-ops/master/rute-carter', AdminOpsMasterController::class)->middleware('permission:master.view')->defaults('tab', 'rute-carter')->defaults('locked', true)->name('admin-ops.master.rute-carter');

    // SaaS Management
    Route::get('admin-ops/saas', \App\Http\Controllers\AdminOpsSaasController::class)->middleware('permission:pool.manage')->name('admin-ops.saas');
    Route::get('admin-ops/saas/tenants', \App\Http\Controllers\AdminOpsSaasController::class)->middleware('permission:pool.manage')->defaults('tab', 'tenants')->name('admin-ops.saas.tenants');
    Route::get('admin-ops/saas/subscriptions', \App\Http\Controllers\AdminOpsSaasController::class)->middleware('permission:pool.manage')->defaults('tab', 'subscriptions')->name('admin-ops.saas.subscriptions');
    Route::get('admin-ops/saas/plans', \App\Http\Controllers\AdminOpsSaasController::class)->middleware('permission:pool.manage')->defaults('tab', 'plans')->name('admin-ops.saas.plans');

    Route::prefix('api/bookings')->name('api.bookings.')->group(function () {
        Route::get('routes-by-date', [BookingApiController::class, 'routesByDate'])->middleware('permission:booking.view')->name('routes-by-date');
        Route::get('schedules', [BookingApiController::class, 'schedules'])->middleware('permission:booking.view')->name('schedules');
        Route::get('seats-detail', [BookingApiController::class, 'bookedSeatsDetail'])->middleware('permission:booking.view')->name('seats-detail');
        Route::get('edit-seat-options', [BookingApiController::class, 'editSeatOptions'])->middleware('permission:booking.view')->name('edit-seat-options');
        Route::get('departure-riturs', [BookingApiController::class, 'departureRiturs'])->middleware('permission:booking.view')->name('departure-riturs');
        Route::post('empty-departure', [BookingApiController::class, 'emptyDeparture'])->middleware('permission:booking.create')->name('empty-departure');
        Route::post('cancel-departure', [BookingApiController::class, 'cancelDeparture'])->middleware('permission:booking.delete')->name('cancel-departure');
        Route::post('arrive-departure', [BookingApiController::class, 'arriveDeparture'])->middleware('permission:booking.update')->name('arrive-departure');
        Route::post('departure-riturs/map', [BookingApiController::class, 'mapDepartureRitur'])->middleware('permission:booking.update')->name('departure-riturs.map');
        Route::post('departure-riturs/unmap', [BookingApiController::class, 'unmapDepartureRitur'])->middleware('permission:booking.update')->name('departure-riturs.unmap');
        Route::post('submit', [BookingApiController::class, 'submit'])->middleware('permission:booking.create')->name('submit');
        Route::post('update', [BookingApiController::class, 'update'])->middleware('permission:booking.update')->name('update');
        Route::post('cancel', [BookingApiController::class, 'cancel'])->middleware('permission:booking.delete')->name('cancel');
    });

    Route::prefix('api/master')->name('api.master.')->group(function () {
        Route::get('charter-routes', [OperationsApiController::class, 'charterRoutes'])->middleware('permission:charter.view,master.view')->name('charter-routes');
        Route::get('segments', [OperationsApiController::class, 'segments'])->middleware('permission:booking.view,master.view')->name('segments');
        Route::get('segment-price', [OperationsApiController::class, 'segmentPrice'])->middleware('permission:booking.view,master.view')->name('segment-price');
        Route::get('units', [OperationsApiController::class, 'units'])->middleware('permission:booking.view,charter.view,master.view,armada.view')->name('units');
        Route::get('armadas', [OperationsApiController::class, 'armadas'])->middleware('permission:charter.view,armada.view,master.view')->name('armadas');
        Route::get('drivers', [OperationsApiController::class, 'drivers'])->middleware('permission:charter.view,driver.view')->name('drivers');
        Route::get('luggage-services', [OperationsApiController::class, 'luggageServices'])->middleware('permission:luggage.view,master.view')->name('luggage-services');
        Route::get('customers/search', [OperationsApiController::class, 'searchCustomers'])->middleware('permission:customer.view,booking.view,charter.view,luggage.view')->name('customers.search');
    });

    Route::prefix('api/ops')->name('api.ops.')->group(function () {
        Route::post('charters', [OperationsApiController::class, 'submitCharter'])->middleware('permission:charter.create')->name('charters.submit');
        Route::post('luggages', [OperationsApiController::class, 'submitLuggage'])->middleware('permission:luggage.create')->name('luggages.submit');
        Route::post('luggages/raw', [OperationsApiController::class, 'submitLuggage'])->middleware('permission:luggage.create')->name('luggages.submit-raw');
    });

    Route::prefix('api/admin')->name('api.admin.')->group(function () {
        Route::post('payments/{source}/{id}', [PaymentController::class, 'update'])->middleware('permission:payment.update,booking.update,charter.update,luggage.update')->name('payments.update');

        Route::get('routes', [AdminOpsApiController::class, 'routesIndex'])->middleware('permission:master.view')->name('routes.index');
        Route::post('routes', [AdminOpsApiController::class, 'routesSave'])->middleware('permission:master.manage')->name('routes.save');
        Route::delete('routes/{id}', [AdminOpsApiController::class, 'routesDelete'])->middleware('permission:master.manage')->name('routes.delete');

        Route::get('schedules', [AdminOpsApiController::class, 'schedulesIndex'])->middleware('permission:master.view')->name('schedules.index');
        Route::post('schedules', [AdminOpsApiController::class, 'schedulesSave'])->middleware('permission:master.manage')->name('schedules.save');
        Route::delete('schedules/{id}', [AdminOpsApiController::class, 'schedulesDelete'])->middleware('permission:master.manage')->name('schedules.delete');

        Route::get('drivers', [AdminOpsApiController::class, 'driversIndex'])->middleware('permission:driver.view')->name('drivers.index');
        Route::post('drivers', [AdminOpsApiController::class, 'driversSave'])->middleware('permission:driver.manage')->name('drivers.save');
        Route::delete('drivers/{id}', [AdminOpsApiController::class, 'driversDelete'])->middleware('permission:driver.manage')->name('drivers.delete');

        Route::get('luggage-services', [AdminOpsApiController::class, 'luggageServicesIndex'])->middleware('permission:master.view')->name('luggage-services.index');
        Route::post('luggage-services', [AdminOpsApiController::class, 'luggageServicesSave'])->middleware('permission:master.manage')->name('luggage-services.save');
        Route::delete('luggage-services/{id}', [AdminOpsApiController::class, 'luggageServicesDelete'])->middleware('permission:master.manage')->name('luggage-services.delete');

        Route::get('segments', [AdminOpsApiController::class, 'segmentsIndex'])->middleware('permission:master.view')->name('segments.index');
        Route::post('segments', [AdminOpsApiController::class, 'segmentsSave'])->middleware('permission:master.manage')->name('segments.save');
        Route::delete('segments/{id}', [AdminOpsApiController::class, 'segmentsDelete'])->middleware('permission:master.manage')->name('segments.delete');

        Route::get('customers/template', [AdminOpsApiController::class, 'customersTemplate'])->middleware('permission:customer.import')->name('customers.template');
        Route::post('customers/import', [AdminOpsApiController::class, 'customersImport'])->middleware('permission:customer.import')->name('customers.import');
        Route::get('customers', [AdminOpsApiController::class, 'customersIndex'])->middleware('permission:customer.view')->name('customers.index');
        Route::post('customers', [AdminOpsApiController::class, 'customersSave'])->middleware('permission:customer.create,customer.update')->name('customers.save');
        Route::delete('customers/{id}', [AdminOpsApiController::class, 'customersDelete'])->middleware('permission:customer.delete')->name('customers.delete');

        Route::get('cancellations', [AdminOpsApiController::class, 'cancellationsIndex'])->middleware('permission:logs.view')->name('cancellations.index');
        Route::get('reports/summary', [AdminOpsApiController::class, 'reportsSummary'])->middleware('permission:report.view')->name('reports.summary');
        Route::get('reports/bookings-csv', [AdminOpsApiController::class, 'reportsBookingsCsv'])->middleware('permission:report.export')->name('reports.bookings-csv');
        Route::get('reports/revenue-csv', [AdminOpsApiController::class, 'reportsRevenueCsv'])->middleware('permission:report.export')->name('reports.revenue-csv');

        Route::get('charters', [AdminOpsApiController::class, 'chartersIndex'])->middleware('permission:charter.view')->name('charters.index');
        Route::get('charters/{id}', [AdminOpsApiController::class, 'chartersShow'])->middleware('permission:charter.view')->name('charters.show');
        Route::post('charters', [AdminOpsApiController::class, 'chartersSave'])->middleware('permission:charter.create,charter.update')->name('charters.save');
        Route::post('charters/bulk-delete', [AdminOpsApiController::class, 'chartersBulkDelete'])->middleware('permission:charter.delete')->name('charters.bulk-delete');
        Route::post('charters/{id}/mark-bop-done', [AdminOpsApiController::class, 'chartersMarkBopDone'])->middleware('permission:charter.update,payment.update')->name('charters.mark-bop-done');
        Route::post('charters/{id}/mark-paid', [AdminOpsApiController::class, 'chartersMarkPaid'])->middleware('permission:charter.update,payment.update')->name('charters.mark-paid');
        Route::post('charters/{id}/mark-done', [AdminOpsApiController::class, 'chartersMarkDone'])->middleware('permission:charter.update')->name('charters.mark-done');
        Route::delete('charters/{id}', [AdminOpsApiController::class, 'chartersDelete'])->middleware('permission:charter.delete')->name('charters.delete');

        Route::get('luggages', [AdminOpsApiController::class, 'luggagesIndex'])->middleware('permission:luggage.view')->name('luggages.index');
        Route::post('luggages', [AdminOpsApiController::class, 'luggagesSave'])->middleware('permission:luggage.create,luggage.update')->name('luggages.save');
        Route::post('luggages/raw', [AdminOpsApiController::class, 'luggagesSave'])->middleware('permission:luggage.create,luggage.update')->name('luggages.save-raw');
        Route::post('luggages/bulk-delete', [AdminOpsApiController::class, 'luggagesBulkDelete'])->middleware('permission:luggage.delete')->name('luggages.bulk-delete');
        Route::post('luggages/bulk-status', [AdminOpsApiController::class, 'luggagesBulkStatus'])->middleware('permission:luggage.update')->name('luggages.bulk-status');
        Route::post('luggages/{id}/mark-paid', [AdminOpsApiController::class, 'luggagesMarkPaid'])->middleware('permission:luggage.update,payment.update')->name('luggages.mark-paid');
        Route::post('luggages/{id}/mark-active', [AdminOpsApiController::class, 'luggagesMarkActive'])->middleware('permission:luggage.update')->name('luggages.mark-active');
        Route::post('luggages/{id}/mark-done', [AdminOpsApiController::class, 'luggagesMarkDone'])->middleware('permission:luggage.update')->name('luggages.mark-done');
        Route::post('luggages/{id}/mark-canceled', [AdminOpsApiController::class, 'luggagesMarkCanceled'])->middleware('permission:luggage.update')->name('luggages.mark-canceled');
        Route::get('luggages/{id}/tracking', [AdminOpsApiController::class, 'luggagesTracking'])->middleware('permission:luggage.view,luggage.tracking')->name('luggages.tracking');
        Route::post('luggages/{id}/tracking', [AdminOpsApiController::class, 'luggagesTrackingAdd'])->middleware('permission:luggage.tracking')->name('luggages.tracking.add');
        Route::delete('luggages/{id}', [AdminOpsApiController::class, 'luggagesDelete'])->middleware('permission:luggage.delete')->name('luggages.delete');

        Route::get('assignments', [AdminOpsApiController::class, 'assignmentsIndex'])->middleware('permission:booking.view')->name('assignments.index');
        Route::post('assignments/conflicts', [AdminOpsApiController::class, 'assignmentsConflicts'])->middleware('permission:booking.update')->name('assignments.conflicts');
        Route::post('assignments', [AdminOpsApiController::class, 'assignmentsSave'])->middleware('permission:booking.update')->name('assignments.save');
        Route::post('assignments/bulk-delete', [AdminOpsApiController::class, 'assignmentsBulkDelete'])->middleware('permission:booking.delete')->name('assignments.bulk-delete');
        Route::delete('assignments/{id}', [AdminOpsApiController::class, 'assignmentsDelete'])->middleware('permission:booking.delete')->name('assignments.delete');

        Route::get('customer-bagasi', [AdminOpsApiController::class, 'customerBagasiIndex'])->middleware('permission:customer.view')->name('customer-bagasi.index');
        Route::post('customer-bagasi', [AdminOpsApiController::class, 'customerBagasiSave'])->middleware('permission:customer.create,customer.update')->name('customer-bagasi.save');
        Route::delete('customer-bagasi/{id}', [AdminOpsApiController::class, 'customerBagasiDelete'])->middleware('permission:customer.delete')->name('customer-bagasi.delete');

        Route::get('customer-charter', [AdminOpsApiController::class, 'customerCharterIndex'])->middleware('permission:customer.view')->name('customer-charter.index');
        Route::post('customer-charter', [AdminOpsApiController::class, 'customerCharterSave'])->middleware('permission:customer.create,customer.update')->name('customer-charter.save');
        Route::delete('customer-charter/{id}', [AdminOpsApiController::class, 'customerCharterDelete'])->middleware('permission:customer.delete')->name('customer-charter.delete');

        Route::get('charter-routes', [AdminOpsApiController::class, 'charterRoutesMasterIndex'])->middleware('permission:master.view')->name('charter-routes.index');
        Route::post('charter-routes', [AdminOpsApiController::class, 'charterRoutesMasterSave'])->middleware('permission:master.manage')->name('charter-routes.save');
        Route::delete('charter-routes/{id}', [AdminOpsApiController::class, 'charterRoutesMasterDelete'])->middleware('permission:master.manage')->name('charter-routes.delete');

        Route::get('units', [AdminOpsApiController::class, 'unitsIndex'])->middleware('permission:master.view')->name('units.index');
        Route::post('units', [AdminOpsApiController::class, 'unitsSave'])->middleware('permission:master.manage')->name('units.save');
        Route::delete('units/{id}', [AdminOpsApiController::class, 'unitsDelete'])->middleware('permission:master.manage')->name('units.delete');

        Route::get('armada-categories', [AdminOpsApiController::class, 'armadaCategoriesIndex'])->middleware('permission:armada.view,master.view')->name('armada-categories.index');
        Route::get('armadas', [AdminOpsApiController::class, 'armadasIndex'])->middleware('permission:armada.view')->name('armadas.index');
        Route::get('armadas/{id}', [AdminOpsApiController::class, 'armadasShow'])->middleware('permission:armada.view')->name('armadas.show');
        Route::post('armadas', [AdminOpsApiController::class, 'armadasSave'])->middleware('permission:armada.manage')->name('armadas.save');
        Route::delete('armadas/{id}', [AdminOpsApiController::class, 'armadasDelete'])->middleware('permission:armada.manage')->name('armadas.delete');

        Route::get('pools', [AdminOpsApiController::class, 'poolsIndex'])->middleware('permission:pool.manage,user.manage,report.view,charter.view,luggage.view')->name('pools.index');
        Route::post('pools', [AdminOpsApiController::class, 'poolsSave'])->middleware('permission:pool.manage')->name('pools.save');
        Route::delete('pools/{id}', [AdminOpsApiController::class, 'poolsDelete'])->middleware('permission:pool.manage')->name('pools.delete');
        Route::post('pool/switch', [AdminOpsApiController::class, 'poolSwitch'])->name('pool.switch');

        Route::get('users', [AdminOpsApiController::class, 'usersIndex'])->middleware('permission:user.manage')->name('users.index');
        Route::post('users', [AdminOpsApiController::class, 'usersSave'])->middleware('permission:user.manage')->name('users.save');
        Route::delete('users/{id}', [AdminOpsApiController::class, 'usersDelete'])->middleware('permission:user.manage')->name('users.delete');

        Route::get('roles', [AdminOpsApiController::class, 'rolesIndex'])->middleware('permission:role.manage')->name('roles.index');
        Route::post('roles', [AdminOpsApiController::class, 'rolesSave'])->middleware('permission:role.manage')->name('roles.save');
        Route::delete('roles/{id}', [AdminOpsApiController::class, 'rolesDelete'])->middleware('permission:role.manage')->name('roles.delete');

        // SaaS Management API
        Route::get('tenants', [AdminOpsApiController::class, 'tenantsIndex'])->middleware('permission:pool.manage')->name('tenants.index');
        Route::post('tenants', [AdminOpsApiController::class, 'tenantsSave'])->middleware('permission:pool.manage')->name('tenants.save');
        Route::delete('tenants/{id}', [AdminOpsApiController::class, 'tenantsDelete'])->middleware('permission:pool.manage')->name('tenants.delete');
        Route::get('subscriptions', [AdminOpsApiController::class, 'subscriptionsIndex'])->middleware('permission:pool.manage')->name('subscriptions.index');
        Route::post('subscriptions', [AdminOpsApiController::class, 'subscriptionsSave'])->middleware('permission:pool.manage')->name('subscriptions.save');
        Route::get('plans', [AdminOpsApiController::class, 'plansIndex'])->middleware('permission:pool.manage')->name('plans.index');
        Route::post('plans', [AdminOpsApiController::class, 'plansSave'])->middleware('permission:pool.manage')->name('plans.save');
        Route::get('invoices', [AdminOpsApiController::class, 'invoicesIndex'])->middleware('permission:pool.manage')->name('invoices.index');
        Route::post('invoices/{id}/mark-paid', [AdminOpsApiController::class, 'invoicesMarkPaid'])->middleware('permission:pool.manage')->name('invoices.mark-paid');
    });
});

require __DIR__.'/settings.php';
