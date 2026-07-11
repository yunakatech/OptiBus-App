import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
export const summary = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summary.url(options),
    method: 'get',
})

summary.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/summary',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
summary.url = (options?: RouteQueryOptions) => {
    return summary.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
summary.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summary.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
summary.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: summary.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
    const summaryForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: summary.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
        summaryForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summary.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::summary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
        summaryForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summary.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    summary.form = summaryForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
export const bookingsCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: bookingsCsv.url(options),
    method: 'get',
})

bookingsCsv.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/bookings-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
bookingsCsv.url = (options?: RouteQueryOptions) => {
    return bookingsCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
bookingsCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: bookingsCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
bookingsCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: bookingsCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
    const bookingsCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: bookingsCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
        bookingsCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: bookingsCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
        bookingsCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: bookingsCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    bookingsCsv.form = bookingsCsvForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
export const revenueCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenueCsv.url(options),
    method: 'get',
})

revenueCsv.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/revenue-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
revenueCsv.url = (options?: RouteQueryOptions) => {
    return revenueCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
revenueCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenueCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
revenueCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: revenueCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
    const revenueCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: revenueCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
        revenueCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenueCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::revenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
        revenueCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenueCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    revenueCsv.form = revenueCsvForm
const reports = {
    summary: Object.assign(summary, summary),
bookingsCsv: Object.assign(bookingsCsv, bookingsCsv),
revenueCsv: Object.assign(revenueCsv, revenueCsv),
}

export default reports