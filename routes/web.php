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
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::inertia('menu', 'Menu')->name('menu.index');
    Route::get('bookings', BookingController::class)->name('bookings.index');
    Route::get('bookings/detail/{groupKey}', BookingController::class)->name('bookings.detail');
    Route::get('bookings/manifest/{groupKey}/print', [BookingController::class, 'printManifest'])->name('bookings.manifest.print');
    Route::get('bookings/manifest/{groupKey}/pdf', [BookingController::class, 'downloadManifestPdf'])->name('bookings.manifest.pdf');
    Route::get('bookings/ticket/{bookingId}/print', [BookingController::class, 'printTicket'])->name('bookings.ticket.print');
    Route::get('bookings/ticket/{bookingId}/pdf', [BookingController::class, 'downloadTicketPdf'])->name('bookings.ticket.pdf');
    Route::get('booking-console', BookingController::class)->name('booking-console.index');
    Route::get('charters', AdminOpsFlowsController::class)->defaults('tab', 'charters')->defaults('locked', true)->name('charters.index');
    Route::get('charters/form', AdminOpsFlowsController::class)->defaults('tab', 'charters')->defaults('mode', 'form')->defaults('locked', true)->name('charters.form');
    Route::get('charters/view/{id}', AdminOpsFlowsController::class)->defaults('tab', 'charters')->defaults('mode', 'view')->defaults('locked', true)->name('charters.view');
    Route::get('charters/{id}/invoice/print', [CharterDocumentController::class, 'print'])->name('charters.invoice.print');
    Route::get('charters/{id}/invoice/pdf', [CharterDocumentController::class, 'pdf'])->name('charters.invoice.pdf');
    Route::get('luggages', AdminOpsFlowsController::class)->defaults('tab', 'luggages')->defaults('locked', true)->name('luggages.index');
    Route::get('luggages/form', AdminOpsFlowsController::class)->defaults('tab', 'luggages')->defaults('mode', 'form')->defaults('locked', true)->name('luggages.form');
    Route::get('luggages/{id}/print', [LuggageDocumentController::class, 'print'])->name('luggages.print');
    Route::get('luggages/{id}/pdf', [LuggageDocumentController::class, 'pdf'])->name('luggages.pdf');
    Route::get('report', AdminOpsController::class)->defaults('tab', 'reports')->defaults('locked', true)->name('report.index');
    Route::get('reports', AdminOpsController::class)->defaults('tab', 'reports')->defaults('locked', true)->name('reports.index');
    Route::get('admin-ops', AdminOpsController::class)->name('admin-ops.index');
    Route::get('admin-ops/routes', AdminOpsController::class)->defaults('tab', 'routes')->defaults('locked', true)->name('admin-ops.routes');
    Route::get('admin-ops/schedules', AdminOpsController::class)->defaults('tab', 'schedules')->defaults('locked', true)->name('admin-ops.schedules');
    Route::get('admin-ops/drivers', AdminOpsController::class)->defaults('tab', 'drivers')->defaults('locked', true)->name('admin-ops.drivers');
    Route::get('admin-ops/services', AdminOpsController::class)->defaults('tab', 'services')->defaults('locked', true)->name('admin-ops.services');
    Route::get('admin-ops/segments', AdminOpsController::class)->defaults('tab', 'segments')->defaults('locked', true)->name('admin-ops.segments');
    Route::get('admin-ops/customers', AdminOpsController::class)->defaults('tab', 'customers')->defaults('locked', true)->name('admin-ops.customers');
    Route::get('admin-ops/units', AdminOpsController::class)->defaults('tab', 'units')->defaults('locked', true)->name('admin-ops.units');
    Route::get('admin-ops/units/layout/{id}', AdminOpsController::class)->defaults('tab', 'units')->defaults('mode', 'layout')->defaults('locked', true)->name('admin-ops.units.layout');
    Route::get('admin-ops/armadas', AdminOpsController::class)->defaults('tab', 'armadas')->defaults('locked', true)->name('admin-ops.armadas');
    Route::get('admin-ops/armadas/view/{id}', AdminOpsController::class)->defaults('tab', 'armadas')->defaults('mode', 'view')->defaults('locked', true)->name('admin-ops.armadas.view');
    Route::get('admin-ops/users', AdminOpsController::class)->defaults('tab', 'users')->defaults('locked', true)->name('admin-ops.users');
    Route::get('admin-ops/cancellations', AdminOpsController::class)->defaults('tab', 'cancellations')->defaults('locked', true)->name('admin-ops.cancellations');
    Route::get('admin-ops/reports', AdminOpsController::class)->defaults('tab', 'reports')->defaults('locked', true)->name('admin-ops.reports');

    Route::get('admin-ops/flows', AdminOpsFlowsController::class)->name('admin-ops.flows');
    Route::get('admin-ops/flows/charters', AdminOpsFlowsController::class)->defaults('tab', 'charters')->defaults('locked', true)->name('admin-ops.flows.charters');
    Route::get('admin-ops/flows/luggages', AdminOpsFlowsController::class)->defaults('tab', 'luggages')->defaults('locked', true)->name('admin-ops.flows.luggages');
    Route::get('admin-ops/flows/assignments', AdminOpsFlowsController::class)->defaults('tab', 'assignments')->defaults('locked', true)->name('admin-ops.flows.assignments');
    Route::get('admin-ops/flows/export', AdminOpsFlowsController::class)->defaults('tab', 'export')->defaults('locked', true)->name('admin-ops.flows.export');

    Route::get('admin-ops/master', AdminOpsMasterController::class)->name('admin-ops.master');
    Route::get('admin-ops/master/customer-bagasi', AdminOpsMasterController::class)->defaults('tab', 'customer-bagasi')->defaults('locked', true)->name('admin-ops.master.customer-bagasi');
    Route::get('admin-ops/master/customer-charter', AdminOpsMasterController::class)->defaults('tab', 'customer-charter')->defaults('locked', true)->name('admin-ops.master.customer-charter');
    Route::get('admin-ops/master/rute-carter', AdminOpsMasterController::class)->defaults('tab', 'rute-carter')->defaults('locked', true)->name('admin-ops.master.rute-carter');

    Route::prefix('api/bookings')->name('api.bookings.')->group(function () {
        Route::get('routes-by-date', [BookingApiController::class, 'routesByDate'])->name('routes-by-date');
        Route::get('schedules', [BookingApiController::class, 'schedules'])->name('schedules');
        Route::get('seats-detail', [BookingApiController::class, 'bookedSeatsDetail'])->name('seats-detail');
        Route::get('edit-seat-options', [BookingApiController::class, 'editSeatOptions'])->name('edit-seat-options');
        Route::get('departure-riturs', [BookingApiController::class, 'departureRiturs'])->name('departure-riturs');
        Route::post('empty-departure', [BookingApiController::class, 'emptyDeparture'])->name('empty-departure');
        Route::post('cancel-departure', [BookingApiController::class, 'cancelDeparture'])->name('cancel-departure');
        Route::post('arrive-departure', [BookingApiController::class, 'arriveDeparture'])->name('arrive-departure');
        Route::post('departure-riturs/map', [BookingApiController::class, 'mapDepartureRitur'])->name('departure-riturs.map');
        Route::post('departure-riturs/unmap', [BookingApiController::class, 'unmapDepartureRitur'])->name('departure-riturs.unmap');
        Route::post('submit', [BookingApiController::class, 'submit'])->name('submit');
        Route::post('update', [BookingApiController::class, 'update'])->name('update');
        Route::post('cancel', [BookingApiController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('api/master')->name('api.master.')->group(function () {
        Route::get('charter-routes', [OperationsApiController::class, 'charterRoutes'])->name('charter-routes');
        Route::get('segments', [OperationsApiController::class, 'segments'])->name('segments');
        Route::get('segment-price', [OperationsApiController::class, 'segmentPrice'])->name('segment-price');
        Route::get('units', [OperationsApiController::class, 'units'])->name('units');
        Route::get('drivers', [OperationsApiController::class, 'drivers'])->name('drivers');
        Route::get('luggage-services', [OperationsApiController::class, 'luggageServices'])->name('luggage-services');
        Route::get('customers/search', [OperationsApiController::class, 'searchCustomers'])->name('customers.search');
    });

    Route::prefix('api/ops')->name('api.ops.')->group(function () {
        Route::post('charters', [OperationsApiController::class, 'submitCharter'])->name('charters.submit');
        Route::post('luggages', [OperationsApiController::class, 'submitLuggage'])->name('luggages.submit');
        Route::post('luggages/raw', [OperationsApiController::class, 'submitLuggage'])->name('luggages.submit-raw');
    });

    Route::prefix('api/admin')->name('api.admin.')->group(function () {
        Route::get('routes', [AdminOpsApiController::class, 'routesIndex'])->name('routes.index');
        Route::post('routes', [AdminOpsApiController::class, 'routesSave'])->name('routes.save');
        Route::delete('routes/{id}', [AdminOpsApiController::class, 'routesDelete'])->name('routes.delete');

        Route::get('schedules', [AdminOpsApiController::class, 'schedulesIndex'])->name('schedules.index');
        Route::post('schedules', [AdminOpsApiController::class, 'schedulesSave'])->name('schedules.save');
        Route::delete('schedules/{id}', [AdminOpsApiController::class, 'schedulesDelete'])->name('schedules.delete');

        Route::get('drivers', [AdminOpsApiController::class, 'driversIndex'])->name('drivers.index');
        Route::post('drivers', [AdminOpsApiController::class, 'driversSave'])->name('drivers.save');
        Route::delete('drivers/{id}', [AdminOpsApiController::class, 'driversDelete'])->name('drivers.delete');

        Route::get('luggage-services', [AdminOpsApiController::class, 'luggageServicesIndex'])->name('luggage-services.index');
        Route::post('luggage-services', [AdminOpsApiController::class, 'luggageServicesSave'])->name('luggage-services.save');
        Route::delete('luggage-services/{id}', [AdminOpsApiController::class, 'luggageServicesDelete'])->name('luggage-services.delete');

        Route::get('segments', [AdminOpsApiController::class, 'segmentsIndex'])->name('segments.index');
        Route::post('segments', [AdminOpsApiController::class, 'segmentsSave'])->name('segments.save');
        Route::delete('segments/{id}', [AdminOpsApiController::class, 'segmentsDelete'])->name('segments.delete');

        Route::get('customers/template', [AdminOpsApiController::class, 'customersTemplate'])->name('customers.template');
        Route::post('customers/import', [AdminOpsApiController::class, 'customersImport'])->name('customers.import');
        Route::get('customers', [AdminOpsApiController::class, 'customersIndex'])->name('customers.index');
        Route::post('customers', [AdminOpsApiController::class, 'customersSave'])->name('customers.save');
        Route::delete('customers/{id}', [AdminOpsApiController::class, 'customersDelete'])->name('customers.delete');

        Route::get('cancellations', [AdminOpsApiController::class, 'cancellationsIndex'])->name('cancellations.index');
        Route::get('reports/summary', [AdminOpsApiController::class, 'reportsSummary'])->name('reports.summary');
        Route::get('reports/bookings-csv', [AdminOpsApiController::class, 'reportsBookingsCsv'])->name('reports.bookings-csv');
        Route::get('reports/revenue-csv', [AdminOpsApiController::class, 'reportsRevenueCsv'])->name('reports.revenue-csv');

        Route::get('charters', [AdminOpsApiController::class, 'chartersIndex'])->name('charters.index');
        Route::get('charters/{id}', [AdminOpsApiController::class, 'chartersShow'])->name('charters.show');
        Route::post('charters', [AdminOpsApiController::class, 'chartersSave'])->name('charters.save');
        Route::post('charters/bulk-delete', [AdminOpsApiController::class, 'chartersBulkDelete'])->name('charters.bulk-delete');
        Route::post('charters/{id}/mark-bop-done', [AdminOpsApiController::class, 'chartersMarkBopDone'])->name('charters.mark-bop-done');
        Route::post('charters/{id}/mark-paid', [AdminOpsApiController::class, 'chartersMarkPaid'])->name('charters.mark-paid');
        Route::post('charters/{id}/mark-done', [AdminOpsApiController::class, 'chartersMarkDone'])->name('charters.mark-done');
        Route::delete('charters/{id}', [AdminOpsApiController::class, 'chartersDelete'])->name('charters.delete');

        Route::get('luggages', [AdminOpsApiController::class, 'luggagesIndex'])->name('luggages.index');
        Route::post('luggages', [AdminOpsApiController::class, 'luggagesSave'])->name('luggages.save');
        Route::post('luggages/raw', [AdminOpsApiController::class, 'luggagesSave'])->name('luggages.save-raw');
        Route::post('luggages/bulk-delete', [AdminOpsApiController::class, 'luggagesBulkDelete'])->name('luggages.bulk-delete');
        Route::post('luggages/bulk-status', [AdminOpsApiController::class, 'luggagesBulkStatus'])->name('luggages.bulk-status');
        Route::post('luggages/{id}/mark-paid', [AdminOpsApiController::class, 'luggagesMarkPaid'])->name('luggages.mark-paid');
        Route::post('luggages/{id}/mark-active', [AdminOpsApiController::class, 'luggagesMarkActive'])->name('luggages.mark-active');
        Route::post('luggages/{id}/mark-done', [AdminOpsApiController::class, 'luggagesMarkDone'])->name('luggages.mark-done');
        Route::post('luggages/{id}/mark-canceled', [AdminOpsApiController::class, 'luggagesMarkCanceled'])->name('luggages.mark-canceled');
        Route::get('luggages/{id}/tracking', [AdminOpsApiController::class, 'luggagesTracking'])->name('luggages.tracking');
        Route::post('luggages/{id}/tracking', [AdminOpsApiController::class, 'luggagesTrackingAdd'])->name('luggages.tracking.add');
        Route::delete('luggages/{id}', [AdminOpsApiController::class, 'luggagesDelete'])->name('luggages.delete');

        Route::get('assignments', [AdminOpsApiController::class, 'assignmentsIndex'])->name('assignments.index');
        Route::post('assignments/conflicts', [AdminOpsApiController::class, 'assignmentsConflicts'])->name('assignments.conflicts');
        Route::post('assignments', [AdminOpsApiController::class, 'assignmentsSave'])->name('assignments.save');
        Route::post('assignments/bulk-delete', [AdminOpsApiController::class, 'assignmentsBulkDelete'])->name('assignments.bulk-delete');
        Route::delete('assignments/{id}', [AdminOpsApiController::class, 'assignmentsDelete'])->name('assignments.delete');

        Route::get('customer-bagasi', [AdminOpsApiController::class, 'customerBagasiIndex'])->name('customer-bagasi.index');
        Route::post('customer-bagasi', [AdminOpsApiController::class, 'customerBagasiSave'])->name('customer-bagasi.save');
        Route::delete('customer-bagasi/{id}', [AdminOpsApiController::class, 'customerBagasiDelete'])->name('customer-bagasi.delete');

        Route::get('customer-charter', [AdminOpsApiController::class, 'customerCharterIndex'])->name('customer-charter.index');
        Route::post('customer-charter', [AdminOpsApiController::class, 'customerCharterSave'])->name('customer-charter.save');
        Route::delete('customer-charter/{id}', [AdminOpsApiController::class, 'customerCharterDelete'])->name('customer-charter.delete');

        Route::get('charter-routes', [AdminOpsApiController::class, 'charterRoutesMasterIndex'])->name('charter-routes.index');
        Route::post('charter-routes', [AdminOpsApiController::class, 'charterRoutesMasterSave'])->name('charter-routes.save');
        Route::delete('charter-routes/{id}', [AdminOpsApiController::class, 'charterRoutesMasterDelete'])->name('charter-routes.delete');

        Route::get('units', [AdminOpsApiController::class, 'unitsIndex'])->name('units.index');
        Route::post('units', [AdminOpsApiController::class, 'unitsSave'])->name('units.save');
        Route::delete('units/{id}', [AdminOpsApiController::class, 'unitsDelete'])->name('units.delete');

        Route::get('armada-categories', [AdminOpsApiController::class, 'armadaCategoriesIndex'])->name('armada-categories.index');
        Route::get('armadas', [AdminOpsApiController::class, 'armadasIndex'])->name('armadas.index');
        Route::get('armadas/{id}', [AdminOpsApiController::class, 'armadasShow'])->name('armadas.show');
        Route::post('armadas', [AdminOpsApiController::class, 'armadasSave'])->name('armadas.save');
        Route::delete('armadas/{id}', [AdminOpsApiController::class, 'armadasDelete'])->name('armadas.delete');

        Route::get('users', [AdminOpsApiController::class, 'usersIndex'])->name('users.index');
        Route::post('users', [AdminOpsApiController::class, 'usersSave'])->name('users.save');
        Route::delete('users/{id}', [AdminOpsApiController::class, 'usersDelete'])->name('users.delete');
    });
});

require __DIR__.'/settings.php';
