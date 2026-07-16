import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
const reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url(options),
    method: 'get',
})

reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.definition = {
    methods: ["get","head"],
    url: '/admin/reports/summary',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url = (options?: RouteQueryOptions) => {
    return reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
    const reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
        reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/admin/reports/summary'
 */
        reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5.form = reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5Form
    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
const reportsSummary528e8c9b8a71a0671676113f7cd06390 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsSummary528e8c9b8a71a0671676113f7cd06390.url(options),
    method: 'get',
})

reportsSummary528e8c9b8a71a0671676113f7cd06390.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/summary',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
reportsSummary528e8c9b8a71a0671676113f7cd06390.url = (options?: RouteQueryOptions) => {
    return reportsSummary528e8c9b8a71a0671676113f7cd06390.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
reportsSummary528e8c9b8a71a0671676113f7cd06390.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsSummary528e8c9b8a71a0671676113f7cd06390.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
reportsSummary528e8c9b8a71a0671676113f7cd06390.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsSummary528e8c9b8a71a0671676113f7cd06390.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
    const reportsSummary528e8c9b8a71a0671676113f7cd06390Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsSummary528e8c9b8a71a0671676113f7cd06390.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
        reportsSummary528e8c9b8a71a0671676113f7cd06390Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsSummary528e8c9b8a71a0671676113f7cd06390.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1383
 * @route '/api/admin/reports/summary'
 */
        reportsSummary528e8c9b8a71a0671676113f7cd06390Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsSummary528e8c9b8a71a0671676113f7cd06390.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsSummary528e8c9b8a71a0671676113f7cd06390.form = reportsSummary528e8c9b8a71a0671676113f7cd06390Form

/**
* Multiple routes resolve to \App\Http\Controllers\Api\AdminOpsApiController::reportsSummary, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `reportsSummary['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const reportsSummary = {
    '/admin/reports/summary': reportsSummaryc473fc0bfde87e12ac9b1b955a3807b5,
    '/api/admin/reports/summary': reportsSummary528e8c9b8a71a0671676113f7cd06390,
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
const reportsBookingsCsva201bfb108c0178addcb7248dace708c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url(options),
    method: 'get',
})

reportsBookingsCsva201bfb108c0178addcb7248dace708c.definition = {
    methods: ["get","head"],
    url: '/admin/reports/bookings-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
reportsBookingsCsva201bfb108c0178addcb7248dace708c.url = (options?: RouteQueryOptions) => {
    return reportsBookingsCsva201bfb108c0178addcb7248dace708c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
reportsBookingsCsva201bfb108c0178addcb7248dace708c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
reportsBookingsCsva201bfb108c0178addcb7248dace708c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
    const reportsBookingsCsva201bfb108c0178addcb7248dace708cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
        reportsBookingsCsva201bfb108c0178addcb7248dace708cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/admin/reports/bookings-csv'
 */
        reportsBookingsCsva201bfb108c0178addcb7248dace708cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsBookingsCsva201bfb108c0178addcb7248dace708c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsBookingsCsva201bfb108c0178addcb7248dace708c.form = reportsBookingsCsva201bfb108c0178addcb7248dace708cForm
    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
const reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url(options),
    method: 'get',
})

reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/bookings-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url = (options?: RouteQueryOptions) => {
    return reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
    const reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
        reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1925
 * @route '/api/admin/reports/bookings-csv'
 */
        reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560.form = reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560Form

/**
* Multiple routes resolve to \App\Http\Controllers\Api\AdminOpsApiController::reportsBookingsCsv, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `reportsBookingsCsv['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const reportsBookingsCsv = {
    '/admin/reports/bookings-csv': reportsBookingsCsva201bfb108c0178addcb7248dace708c,
    '/api/admin/reports/bookings-csv': reportsBookingsCsvf06fa765b3d7784e8a116b399b9a9560,
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
const reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url(options),
    method: 'get',
})

reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.definition = {
    methods: ["get","head"],
    url: '/admin/reports/revenue-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url = (options?: RouteQueryOptions) => {
    return reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
    const reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bcForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
        reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bcForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/admin/reports/revenue-csv'
 */
        reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bcForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc.form = reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bcForm
    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
const reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url(options),
    method: 'get',
})

reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.definition = {
    methods: ["get","head"],
    url: '/api/admin/reports/revenue-csv',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url = (options?: RouteQueryOptions) => {
    return reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
    const reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
        reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1998
 * @route '/api/admin/reports/revenue-csv'
 */
        reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d.form = reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7dForm

/**
* Multiple routes resolve to \App\Http\Controllers\Api\AdminOpsApiController::reportsRevenueCsv, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `reportsRevenueCsv['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const reportsRevenueCsv = {
    '/admin/reports/revenue-csv': reportsRevenueCsv81ac2bcf72d81e2325b4eb8be87f95bc,
    '/api/admin/reports/revenue-csv': reportsRevenueCsv7b3662c869ddfd492e6befbd87fa6c7d,
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
export const routesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routesIndex.url(options),
    method: 'get',
})

routesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/routes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
routesIndex.url = (options?: RouteQueryOptions) => {
    return routesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
routesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
routesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: routesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
    const routesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: routesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
        routesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:87
 * @route '/api/admin/routes'
 */
        routesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    routesIndex.form = routesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:113
 * @route '/api/admin/routes'
 */
export const routesSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: routesSave.url(options),
    method: 'post',
})

routesSave.definition = {
    methods: ["post"],
    url: '/api/admin/routes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:113
 * @route '/api/admin/routes'
 */
routesSave.url = (options?: RouteQueryOptions) => {
    return routesSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:113
 * @route '/api/admin/routes'
 */
routesSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: routesSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:113
 * @route '/api/admin/routes'
 */
    const routesSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: routesSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:113
 * @route '/api/admin/routes'
 */
        routesSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: routesSave.url(options),
            method: 'post',
        })

    routesSave.form = routesSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:176
 * @route '/api/admin/routes/{id}'
 */
export const routesDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: routesDelete.url(args, options),
    method: 'delete',
})

routesDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/routes/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:176
 * @route '/api/admin/routes/{id}'
 */
routesDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return routesDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:176
 * @route '/api/admin/routes/{id}'
 */
routesDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: routesDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:176
 * @route '/api/admin/routes/{id}'
 */
    const routesDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: routesDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::routesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:176
 * @route '/api/admin/routes/{id}'
 */
        routesDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: routesDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    routesDelete.form = routesDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
export const schedulesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedulesIndex.url(options),
    method: 'get',
})

schedulesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
schedulesIndex.url = (options?: RouteQueryOptions) => {
    return schedulesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
schedulesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedulesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
schedulesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: schedulesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
    const schedulesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: schedulesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
        schedulesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedulesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:194
 * @route '/api/admin/schedules'
 */
        schedulesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedulesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    schedulesIndex.form = schedulesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:381
 * @route '/api/admin/schedules'
 */
export const schedulesSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: schedulesSave.url(options),
    method: 'post',
})

schedulesSave.definition = {
    methods: ["post"],
    url: '/api/admin/schedules',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:381
 * @route '/api/admin/schedules'
 */
schedulesSave.url = (options?: RouteQueryOptions) => {
    return schedulesSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:381
 * @route '/api/admin/schedules'
 */
schedulesSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: schedulesSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:381
 * @route '/api/admin/schedules'
 */
    const schedulesSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: schedulesSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:381
 * @route '/api/admin/schedules'
 */
        schedulesSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: schedulesSave.url(options),
            method: 'post',
        })

    schedulesSave.form = schedulesSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:607
 * @route '/api/admin/schedules/{id}'
 */
export const schedulesDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: schedulesDelete.url(args, options),
    method: 'delete',
})

schedulesDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/schedules/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:607
 * @route '/api/admin/schedules/{id}'
 */
schedulesDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return schedulesDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:607
 * @route '/api/admin/schedules/{id}'
 */
schedulesDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: schedulesDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:607
 * @route '/api/admin/schedules/{id}'
 */
    const schedulesDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: schedulesDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::schedulesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:607
 * @route '/api/admin/schedules/{id}'
 */
        schedulesDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: schedulesDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    schedulesDelete.form = schedulesDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
export const driversIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: driversIndex.url(options),
    method: 'get',
})

driversIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/drivers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
driversIndex.url = (options?: RouteQueryOptions) => {
    return driversIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
driversIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: driversIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
driversIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: driversIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
    const driversIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: driversIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
        driversIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: driversIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:631
 * @route '/api/admin/drivers'
 */
        driversIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: driversIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    driversIndex.form = driversIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:734
 * @route '/api/admin/drivers'
 */
export const driversSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: driversSave.url(options),
    method: 'post',
})

driversSave.definition = {
    methods: ["post"],
    url: '/api/admin/drivers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:734
 * @route '/api/admin/drivers'
 */
driversSave.url = (options?: RouteQueryOptions) => {
    return driversSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:734
 * @route '/api/admin/drivers'
 */
driversSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: driversSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:734
 * @route '/api/admin/drivers'
 */
    const driversSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: driversSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:734
 * @route '/api/admin/drivers'
 */
        driversSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: driversSave.url(options),
            method: 'post',
        })

    driversSave.form = driversSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:846
 * @route '/api/admin/drivers/{id}'
 */
export const driversDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: driversDelete.url(args, options),
    method: 'delete',
})

driversDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/drivers/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:846
 * @route '/api/admin/drivers/{id}'
 */
driversDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return driversDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:846
 * @route '/api/admin/drivers/{id}'
 */
driversDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: driversDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:846
 * @route '/api/admin/drivers/{id}'
 */
    const driversDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: driversDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:846
 * @route '/api/admin/drivers/{id}'
 */
        driversDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: driversDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    driversDelete.form = driversDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
export const luggageServicesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggageServicesIndex.url(options),
    method: 'get',
})

luggageServicesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggage-services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
luggageServicesIndex.url = (options?: RouteQueryOptions) => {
    return luggageServicesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
luggageServicesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggageServicesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
luggageServicesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: luggageServicesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
    const luggageServicesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: luggageServicesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
        luggageServicesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggageServicesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:863
 * @route '/api/admin/luggage-services'
 */
        luggageServicesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggageServicesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    luggageServicesIndex.form = luggageServicesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:878
 * @route '/api/admin/luggage-services'
 */
export const luggageServicesSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggageServicesSave.url(options),
    method: 'post',
})

luggageServicesSave.definition = {
    methods: ["post"],
    url: '/api/admin/luggage-services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:878
 * @route '/api/admin/luggage-services'
 */
luggageServicesSave.url = (options?: RouteQueryOptions) => {
    return luggageServicesSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:878
 * @route '/api/admin/luggage-services'
 */
luggageServicesSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggageServicesSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:878
 * @route '/api/admin/luggage-services'
 */
    const luggageServicesSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggageServicesSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:878
 * @route '/api/admin/luggage-services'
 */
        luggageServicesSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggageServicesSave.url(options),
            method: 'post',
        })

    luggageServicesSave.form = luggageServicesSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:906
 * @route '/api/admin/luggage-services/{id}'
 */
export const luggageServicesDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: luggageServicesDelete.url(args, options),
    method: 'delete',
})

luggageServicesDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/luggage-services/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:906
 * @route '/api/admin/luggage-services/{id}'
 */
luggageServicesDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggageServicesDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:906
 * @route '/api/admin/luggage-services/{id}'
 */
luggageServicesDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: luggageServicesDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:906
 * @route '/api/admin/luggage-services/{id}'
 */
    const luggageServicesDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggageServicesDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggageServicesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:906
 * @route '/api/admin/luggage-services/{id}'
 */
        luggageServicesDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggageServicesDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    luggageServicesDelete.form = luggageServicesDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
export const segmentsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segmentsIndex.url(options),
    method: 'get',
})

segmentsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
segmentsIndex.url = (options?: RouteQueryOptions) => {
    return segmentsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
segmentsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segmentsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
segmentsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: segmentsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
    const segmentsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: segmentsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
        segmentsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segmentsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:916
 * @route '/api/admin/segments'
 */
        segmentsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segmentsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    segmentsIndex.form = segmentsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:996
 * @route '/api/admin/segments'
 */
export const segmentsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: segmentsSave.url(options),
    method: 'post',
})

segmentsSave.definition = {
    methods: ["post"],
    url: '/api/admin/segments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:996
 * @route '/api/admin/segments'
 */
segmentsSave.url = (options?: RouteQueryOptions) => {
    return segmentsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:996
 * @route '/api/admin/segments'
 */
segmentsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: segmentsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:996
 * @route '/api/admin/segments'
 */
    const segmentsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: segmentsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:996
 * @route '/api/admin/segments'
 */
        segmentsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: segmentsSave.url(options),
            method: 'post',
        })

    segmentsSave.form = segmentsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1071
 * @route '/api/admin/segments/{id}'
 */
export const segmentsDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: segmentsDelete.url(args, options),
    method: 'delete',
})

segmentsDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/segments/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1071
 * @route '/api/admin/segments/{id}'
 */
segmentsDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return segmentsDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1071
 * @route '/api/admin/segments/{id}'
 */
segmentsDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: segmentsDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1071
 * @route '/api/admin/segments/{id}'
 */
    const segmentsDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: segmentsDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::segmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1071
 * @route '/api/admin/segments/{id}'
 */
        segmentsDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: segmentsDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    segmentsDelete.form = segmentsDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
export const customersTemplate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersTemplate.url(options),
    method: 'get',
})

customersTemplate.definition = {
    methods: ["get","head"],
    url: '/api/admin/customers/template',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
customersTemplate.url = (options?: RouteQueryOptions) => {
    return customersTemplate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
customersTemplate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersTemplate.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
customersTemplate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customersTemplate.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
    const customersTemplateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customersTemplate.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
        customersTemplateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersTemplate.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersTemplate
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1234
 * @route '/api/admin/customers/template'
 */
        customersTemplateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersTemplate.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    customersTemplate.form = customersTemplateForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersImport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1245
 * @route '/api/admin/customers/import'
 */
export const customersImport = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersImport.url(options),
    method: 'post',
})

customersImport.definition = {
    methods: ["post"],
    url: '/api/admin/customers/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersImport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1245
 * @route '/api/admin/customers/import'
 */
customersImport.url = (options?: RouteQueryOptions) => {
    return customersImport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersImport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1245
 * @route '/api/admin/customers/import'
 */
customersImport.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersImport.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersImport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1245
 * @route '/api/admin/customers/import'
 */
    const customersImportForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customersImport.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersImport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1245
 * @route '/api/admin/customers/import'
 */
        customersImportForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customersImport.url(options),
            method: 'post',
        })

    customersImport.form = customersImportForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
export const customersIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersIndex.url(options),
    method: 'get',
})

customersIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
customersIndex.url = (options?: RouteQueryOptions) => {
    return customersIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
customersIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
customersIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customersIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
    const customersIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customersIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
        customersIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1084
 * @route '/api/admin/customers'
 */
        customersIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    customersIndex.form = customersIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1143
 * @route '/api/admin/customers'
 */
export const customersSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersSave.url(options),
    method: 'post',
})

customersSave.definition = {
    methods: ["post"],
    url: '/api/admin/customers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1143
 * @route '/api/admin/customers'
 */
customersSave.url = (options?: RouteQueryOptions) => {
    return customersSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1143
 * @route '/api/admin/customers'
 */
customersSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1143
 * @route '/api/admin/customers'
 */
    const customersSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customersSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1143
 * @route '/api/admin/customers'
 */
        customersSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customersSave.url(options),
            method: 'post',
        })

    customersSave.form = customersSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1220
 * @route '/api/admin/customers/{id}'
 */
export const customersDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customersDelete.url(args, options),
    method: 'delete',
})

customersDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/customers/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1220
 * @route '/api/admin/customers/{id}'
 */
customersDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return customersDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1220
 * @route '/api/admin/customers/{id}'
 */
customersDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customersDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1220
 * @route '/api/admin/customers/{id}'
 */
    const customersDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customersDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1220
 * @route '/api/admin/customers/{id}'
 */
        customersDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customersDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    customersDelete.form = customersDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
export const activityLogsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: activityLogsIndex.url(options),
    method: 'get',
})

activityLogsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/activity-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
activityLogsIndex.url = (options?: RouteQueryOptions) => {
    return activityLogsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
activityLogsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: activityLogsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
activityLogsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: activityLogsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
    const activityLogsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: activityLogsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
        activityLogsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: activityLogsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::activityLogsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1358
 * @route '/api/admin/activity-logs'
 */
        activityLogsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: activityLogsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    activityLogsIndex.form = activityLogsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
export const armadasExport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasExport.url(options),
    method: 'get',
})

armadasExport.definition = {
    methods: ["get","head"],
    url: '/api/admin/armadas/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
armadasExport.url = (options?: RouteQueryOptions) => {
    return armadasExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
armadasExport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasExport.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
armadasExport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadasExport.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
    const armadasExportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadasExport.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
        armadasExportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasExport.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4492
 * @route '/api/admin/armadas/export'
 */
        armadasExportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasExport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    armadasExport.form = armadasExportForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
export const driversExport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: driversExport.url(options),
    method: 'get',
})

driversExport.definition = {
    methods: ["get","head"],
    url: '/api/admin/drivers/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
driversExport.url = (options?: RouteQueryOptions) => {
    return driversExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
driversExport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: driversExport.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
driversExport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: driversExport.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
    const driversExportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: driversExport.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
        driversExportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: driversExport.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::driversExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:671
 * @route '/api/admin/drivers/export'
 */
        driversExportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: driversExport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    driversExport.form = driversExportForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
export const chartersIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chartersIndex.url(options),
    method: 'get',
})

chartersIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
chartersIndex.url = (options?: RouteQueryOptions) => {
    return chartersIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
chartersIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chartersIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
chartersIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: chartersIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
    const chartersIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: chartersIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
        chartersIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chartersIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2146
 * @route '/api/admin/charters'
 */
        chartersIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chartersIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    chartersIndex.form = chartersIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
export const chartersShow = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chartersShow.url(args, options),
    method: 'get',
})

chartersShow.definition = {
    methods: ["get","head"],
    url: '/api/admin/charters/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
chartersShow.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return chartersShow.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
chartersShow.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chartersShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
chartersShow.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: chartersShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
    const chartersShowForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: chartersShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
        chartersShowForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chartersShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2406
 * @route '/api/admin/charters/{id}'
 */
        chartersShowForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chartersShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    chartersShow.form = chartersShowForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2477
 * @route '/api/admin/charters'
 */
export const chartersSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersSave.url(options),
    method: 'post',
})

chartersSave.definition = {
    methods: ["post"],
    url: '/api/admin/charters',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2477
 * @route '/api/admin/charters'
 */
chartersSave.url = (options?: RouteQueryOptions) => {
    return chartersSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2477
 * @route '/api/admin/charters'
 */
chartersSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2477
 * @route '/api/admin/charters'
 */
    const chartersSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2477
 * @route '/api/admin/charters'
 */
        chartersSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersSave.url(options),
            method: 'post',
        })

    chartersSave.form = chartersSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2768
 * @route '/api/admin/charters/bulk-delete'
 */
export const chartersBulkDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersBulkDelete.url(options),
    method: 'post',
})

chartersBulkDelete.definition = {
    methods: ["post"],
    url: '/api/admin/charters/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2768
 * @route '/api/admin/charters/bulk-delete'
 */
chartersBulkDelete.url = (options?: RouteQueryOptions) => {
    return chartersBulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2768
 * @route '/api/admin/charters/bulk-delete'
 */
chartersBulkDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersBulkDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2768
 * @route '/api/admin/charters/bulk-delete'
 */
    const chartersBulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersBulkDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2768
 * @route '/api/admin/charters/bulk-delete'
 */
        chartersBulkDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersBulkDelete.url(options),
            method: 'post',
        })

    chartersBulkDelete.form = chartersBulkDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkBopDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2795
 * @route '/api/admin/charters/{id}/mark-bop-done'
 */
export const chartersMarkBopDone = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkBopDone.url(args, options),
    method: 'post',
})

chartersMarkBopDone.definition = {
    methods: ["post"],
    url: '/api/admin/charters/{id}/mark-bop-done',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkBopDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2795
 * @route '/api/admin/charters/{id}/mark-bop-done'
 */
chartersMarkBopDone.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return chartersMarkBopDone.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkBopDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2795
 * @route '/api/admin/charters/{id}/mark-bop-done'
 */
chartersMarkBopDone.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkBopDone.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkBopDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2795
 * @route '/api/admin/charters/{id}/mark-bop-done'
 */
    const chartersMarkBopDoneForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersMarkBopDone.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkBopDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2795
 * @route '/api/admin/charters/{id}/mark-bop-done'
 */
        chartersMarkBopDoneForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersMarkBopDone.url(args, options),
            method: 'post',
        })

    chartersMarkBopDone.form = chartersMarkBopDoneForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2823
 * @route '/api/admin/charters/{id}/mark-paid'
 */
export const chartersMarkPaid = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkPaid.url(args, options),
    method: 'post',
})

chartersMarkPaid.definition = {
    methods: ["post"],
    url: '/api/admin/charters/{id}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2823
 * @route '/api/admin/charters/{id}/mark-paid'
 */
chartersMarkPaid.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return chartersMarkPaid.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2823
 * @route '/api/admin/charters/{id}/mark-paid'
 */
chartersMarkPaid.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkPaid.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2823
 * @route '/api/admin/charters/{id}/mark-paid'
 */
    const chartersMarkPaidForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersMarkPaid.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2823
 * @route '/api/admin/charters/{id}/mark-paid'
 */
        chartersMarkPaidForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersMarkPaid.url(args, options),
            method: 'post',
        })

    chartersMarkPaid.form = chartersMarkPaidForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2851
 * @route '/api/admin/charters/{id}/mark-done'
 */
export const chartersMarkDone = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkDone.url(args, options),
    method: 'post',
})

chartersMarkDone.definition = {
    methods: ["post"],
    url: '/api/admin/charters/{id}/mark-done',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2851
 * @route '/api/admin/charters/{id}/mark-done'
 */
chartersMarkDone.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return chartersMarkDone.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2851
 * @route '/api/admin/charters/{id}/mark-done'
 */
chartersMarkDone.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chartersMarkDone.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2851
 * @route '/api/admin/charters/{id}/mark-done'
 */
    const chartersMarkDoneForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersMarkDone.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2851
 * @route '/api/admin/charters/{id}/mark-done'
 */
        chartersMarkDoneForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersMarkDone.url(args, options),
            method: 'post',
        })

    chartersMarkDone.form = chartersMarkDoneForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2712
 * @route '/api/admin/charters/{id}'
 */
export const chartersDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: chartersDelete.url(args, options),
    method: 'delete',
})

chartersDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/charters/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2712
 * @route '/api/admin/charters/{id}'
 */
chartersDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return chartersDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2712
 * @route '/api/admin/charters/{id}'
 */
chartersDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: chartersDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2712
 * @route '/api/admin/charters/{id}'
 */
    const chartersDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chartersDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::chartersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2712
 * @route '/api/admin/charters/{id}'
 */
        chartersDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chartersDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    chartersDelete.form = chartersDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
export const luggagesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggagesIndex.url(options),
    method: 'get',
})

luggagesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
luggagesIndex.url = (options?: RouteQueryOptions) => {
    return luggagesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
luggagesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggagesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
luggagesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: luggagesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
    const luggagesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: luggagesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
        luggagesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggagesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2899
 * @route '/api/admin/luggages'
 */
        luggagesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggagesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    luggagesIndex.form = luggagesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages'
 */
const luggagesSave221d72786cac56670a26934bad8d7db1 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesSave221d72786cac56670a26934bad8d7db1.url(options),
    method: 'post',
})

luggagesSave221d72786cac56670a26934bad8d7db1.definition = {
    methods: ["post"],
    url: '/api/admin/luggages',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages'
 */
luggagesSave221d72786cac56670a26934bad8d7db1.url = (options?: RouteQueryOptions) => {
    return luggagesSave221d72786cac56670a26934bad8d7db1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages'
 */
luggagesSave221d72786cac56670a26934bad8d7db1.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesSave221d72786cac56670a26934bad8d7db1.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages'
 */
    const luggagesSave221d72786cac56670a26934bad8d7db1Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesSave221d72786cac56670a26934bad8d7db1.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages'
 */
        luggagesSave221d72786cac56670a26934bad8d7db1Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesSave221d72786cac56670a26934bad8d7db1.url(options),
            method: 'post',
        })

    luggagesSave221d72786cac56670a26934bad8d7db1.form = luggagesSave221d72786cac56670a26934bad8d7db1Form
    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages/raw'
 */
const luggagesSavee3e8cf06945b592401b1043df066e98c = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesSavee3e8cf06945b592401b1043df066e98c.url(options),
    method: 'post',
})

luggagesSavee3e8cf06945b592401b1043df066e98c.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/raw',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages/raw'
 */
luggagesSavee3e8cf06945b592401b1043df066e98c.url = (options?: RouteQueryOptions) => {
    return luggagesSavee3e8cf06945b592401b1043df066e98c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages/raw'
 */
luggagesSavee3e8cf06945b592401b1043df066e98c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesSavee3e8cf06945b592401b1043df066e98c.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages/raw'
 */
    const luggagesSavee3e8cf06945b592401b1043df066e98cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesSavee3e8cf06945b592401b1043df066e98c.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3107
 * @route '/api/admin/luggages/raw'
 */
        luggagesSavee3e8cf06945b592401b1043df066e98cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesSavee3e8cf06945b592401b1043df066e98c.url(options),
            method: 'post',
        })

    luggagesSavee3e8cf06945b592401b1043df066e98c.form = luggagesSavee3e8cf06945b592401b1043df066e98cForm

/**
* Multiple routes resolve to \App\Http\Controllers\Api\AdminOpsApiController::luggagesSave, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `luggagesSave['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const luggagesSave = {
    '/api/admin/luggages': luggagesSave221d72786cac56670a26934bad8d7db1,
    '/api/admin/luggages/raw': luggagesSavee3e8cf06945b592401b1043df066e98c,
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3271
 * @route '/api/admin/luggages/bulk-delete'
 */
export const luggagesBulkDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesBulkDelete.url(options),
    method: 'post',
})

luggagesBulkDelete.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3271
 * @route '/api/admin/luggages/bulk-delete'
 */
luggagesBulkDelete.url = (options?: RouteQueryOptions) => {
    return luggagesBulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3271
 * @route '/api/admin/luggages/bulk-delete'
 */
luggagesBulkDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesBulkDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3271
 * @route '/api/admin/luggages/bulk-delete'
 */
    const luggagesBulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesBulkDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3271
 * @route '/api/admin/luggages/bulk-delete'
 */
        luggagesBulkDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesBulkDelete.url(options),
            method: 'post',
        })

    luggagesBulkDelete.form = luggagesBulkDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3288
 * @route '/api/admin/luggages/bulk-status'
 */
export const luggagesBulkStatus = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesBulkStatus.url(options),
    method: 'post',
})

luggagesBulkStatus.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/bulk-status',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3288
 * @route '/api/admin/luggages/bulk-status'
 */
luggagesBulkStatus.url = (options?: RouteQueryOptions) => {
    return luggagesBulkStatus.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3288
 * @route '/api/admin/luggages/bulk-status'
 */
luggagesBulkStatus.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesBulkStatus.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3288
 * @route '/api/admin/luggages/bulk-status'
 */
    const luggagesBulkStatusForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesBulkStatus.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesBulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3288
 * @route '/api/admin/luggages/bulk-status'
 */
        luggagesBulkStatusForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesBulkStatus.url(options),
            method: 'post',
        })

    luggagesBulkStatus.form = luggagesBulkStatusForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3335
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
export const luggagesMarkPaid = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkPaid.url(args, options),
    method: 'post',
})

luggagesMarkPaid.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3335
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
luggagesMarkPaid.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesMarkPaid.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3335
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
luggagesMarkPaid.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkPaid.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3335
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
    const luggagesMarkPaidForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesMarkPaid.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3335
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
        luggagesMarkPaidForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesMarkPaid.url(args, options),
            method: 'post',
        })

    luggagesMarkPaid.form = luggagesMarkPaidForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3362
 * @route '/api/admin/luggages/{id}/mark-active'
 */
export const luggagesMarkActive = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkActive.url(args, options),
    method: 'post',
})

luggagesMarkActive.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-active',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3362
 * @route '/api/admin/luggages/{id}/mark-active'
 */
luggagesMarkActive.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesMarkActive.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3362
 * @route '/api/admin/luggages/{id}/mark-active'
 */
luggagesMarkActive.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkActive.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3362
 * @route '/api/admin/luggages/{id}/mark-active'
 */
    const luggagesMarkActiveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesMarkActive.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3362
 * @route '/api/admin/luggages/{id}/mark-active'
 */
        luggagesMarkActiveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesMarkActive.url(args, options),
            method: 'post',
        })

    luggagesMarkActive.form = luggagesMarkActiveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3390
 * @route '/api/admin/luggages/{id}/mark-done'
 */
export const luggagesMarkDone = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkDone.url(args, options),
    method: 'post',
})

luggagesMarkDone.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-done',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3390
 * @route '/api/admin/luggages/{id}/mark-done'
 */
luggagesMarkDone.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesMarkDone.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3390
 * @route '/api/admin/luggages/{id}/mark-done'
 */
luggagesMarkDone.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkDone.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3390
 * @route '/api/admin/luggages/{id}/mark-done'
 */
    const luggagesMarkDoneForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesMarkDone.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3390
 * @route '/api/admin/luggages/{id}/mark-done'
 */
        luggagesMarkDoneForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesMarkDone.url(args, options),
            method: 'post',
        })

    luggagesMarkDone.form = luggagesMarkDoneForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3418
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
export const luggagesMarkCanceled = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkCanceled.url(args, options),
    method: 'post',
})

luggagesMarkCanceled.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-canceled',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3418
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
luggagesMarkCanceled.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesMarkCanceled.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3418
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
luggagesMarkCanceled.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesMarkCanceled.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3418
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
    const luggagesMarkCanceledForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesMarkCanceled.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesMarkCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3418
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
        luggagesMarkCanceledForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesMarkCanceled.url(args, options),
            method: 'post',
        })

    luggagesMarkCanceled.form = luggagesMarkCanceledForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
export const luggagesTracking = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggagesTracking.url(args, options),
    method: 'get',
})

luggagesTracking.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggages/{id}/tracking',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
luggagesTracking.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesTracking.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
luggagesTracking.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggagesTracking.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
luggagesTracking.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: luggagesTracking.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
    const luggagesTrackingForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: luggagesTracking.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
        luggagesTrackingForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggagesTracking.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3448
 * @route '/api/admin/luggages/{id}/tracking'
 */
        luggagesTrackingForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggagesTracking.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    luggagesTracking.form = luggagesTrackingForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTrackingAdd
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
export const luggagesTrackingAdd = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesTrackingAdd.url(args, options),
    method: 'post',
})

luggagesTrackingAdd.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/tracking',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTrackingAdd
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
luggagesTrackingAdd.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesTrackingAdd.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTrackingAdd
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
luggagesTrackingAdd.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: luggagesTrackingAdd.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTrackingAdd
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
    const luggagesTrackingAddForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesTrackingAdd.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesTrackingAdd
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
        luggagesTrackingAddForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesTrackingAdd.url(args, options),
            method: 'post',
        })

    luggagesTrackingAdd.form = luggagesTrackingAddForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3251
 * @route '/api/admin/luggages/{id}'
 */
export const luggagesDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: luggagesDelete.url(args, options),
    method: 'delete',
})

luggagesDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/luggages/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3251
 * @route '/api/admin/luggages/{id}'
 */
luggagesDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return luggagesDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3251
 * @route '/api/admin/luggages/{id}'
 */
luggagesDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: luggagesDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3251
 * @route '/api/admin/luggages/{id}'
 */
    const luggagesDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: luggagesDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::luggagesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3251
 * @route '/api/admin/luggages/{id}'
 */
        luggagesDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: luggagesDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    luggagesDelete.form = luggagesDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
export const assignmentsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assignmentsIndex.url(options),
    method: 'get',
})

assignmentsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/assignments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
assignmentsIndex.url = (options?: RouteQueryOptions) => {
    return assignmentsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
assignmentsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assignmentsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
assignmentsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assignmentsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
    const assignmentsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: assignmentsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
        assignmentsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assignmentsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3537
 * @route '/api/admin/assignments'
 */
        assignmentsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assignmentsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    assignmentsIndex.form = assignmentsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsConflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3599
 * @route '/api/admin/assignments/conflicts'
 */
export const assignmentsConflicts = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsConflicts.url(options),
    method: 'post',
})

assignmentsConflicts.definition = {
    methods: ["post"],
    url: '/api/admin/assignments/conflicts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsConflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3599
 * @route '/api/admin/assignments/conflicts'
 */
assignmentsConflicts.url = (options?: RouteQueryOptions) => {
    return assignmentsConflicts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsConflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3599
 * @route '/api/admin/assignments/conflicts'
 */
assignmentsConflicts.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsConflicts.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsConflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3599
 * @route '/api/admin/assignments/conflicts'
 */
    const assignmentsConflictsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: assignmentsConflicts.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsConflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3599
 * @route '/api/admin/assignments/conflicts'
 */
        assignmentsConflictsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: assignmentsConflicts.url(options),
            method: 'post',
        })

    assignmentsConflicts.form = assignmentsConflictsForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3623
 * @route '/api/admin/assignments'
 */
export const assignmentsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsSave.url(options),
    method: 'post',
})

assignmentsSave.definition = {
    methods: ["post"],
    url: '/api/admin/assignments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3623
 * @route '/api/admin/assignments'
 */
assignmentsSave.url = (options?: RouteQueryOptions) => {
    return assignmentsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3623
 * @route '/api/admin/assignments'
 */
assignmentsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3623
 * @route '/api/admin/assignments'
 */
    const assignmentsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: assignmentsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3623
 * @route '/api/admin/assignments'
 */
        assignmentsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: assignmentsSave.url(options),
            method: 'post',
        })

    assignmentsSave.form = assignmentsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3814
 * @route '/api/admin/assignments/bulk-delete'
 */
export const assignmentsBulkDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsBulkDelete.url(options),
    method: 'post',
})

assignmentsBulkDelete.definition = {
    methods: ["post"],
    url: '/api/admin/assignments/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3814
 * @route '/api/admin/assignments/bulk-delete'
 */
assignmentsBulkDelete.url = (options?: RouteQueryOptions) => {
    return assignmentsBulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3814
 * @route '/api/admin/assignments/bulk-delete'
 */
assignmentsBulkDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignmentsBulkDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3814
 * @route '/api/admin/assignments/bulk-delete'
 */
    const assignmentsBulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: assignmentsBulkDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsBulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3814
 * @route '/api/admin/assignments/bulk-delete'
 */
        assignmentsBulkDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: assignmentsBulkDelete.url(options),
            method: 'post',
        })

    assignmentsBulkDelete.form = assignmentsBulkDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3802
 * @route '/api/admin/assignments/{id}'
 */
export const assignmentsDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: assignmentsDelete.url(args, options),
    method: 'delete',
})

assignmentsDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/assignments/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3802
 * @route '/api/admin/assignments/{id}'
 */
assignmentsDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return assignmentsDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3802
 * @route '/api/admin/assignments/{id}'
 */
assignmentsDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: assignmentsDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3802
 * @route '/api/admin/assignments/{id}'
 */
    const assignmentsDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: assignmentsDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::assignmentsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3802
 * @route '/api/admin/assignments/{id}'
 */
        assignmentsDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: assignmentsDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    assignmentsDelete.form = assignmentsDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
export const customerBagasiIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerBagasiIndex.url(options),
    method: 'get',
})

customerBagasiIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/customer-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
customerBagasiIndex.url = (options?: RouteQueryOptions) => {
    return customerBagasiIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
customerBagasiIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerBagasiIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
customerBagasiIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customerBagasiIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
    const customerBagasiIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customerBagasiIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
        customerBagasiIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerBagasiIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3836
 * @route '/api/admin/customer-bagasi'
 */
        customerBagasiIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerBagasiIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    customerBagasiIndex.form = customerBagasiIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3873
 * @route '/api/admin/customer-bagasi'
 */
export const customerBagasiSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customerBagasiSave.url(options),
    method: 'post',
})

customerBagasiSave.definition = {
    methods: ["post"],
    url: '/api/admin/customer-bagasi',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3873
 * @route '/api/admin/customer-bagasi'
 */
customerBagasiSave.url = (options?: RouteQueryOptions) => {
    return customerBagasiSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3873
 * @route '/api/admin/customer-bagasi'
 */
customerBagasiSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customerBagasiSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3873
 * @route '/api/admin/customer-bagasi'
 */
    const customerBagasiSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customerBagasiSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3873
 * @route '/api/admin/customer-bagasi'
 */
        customerBagasiSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customerBagasiSave.url(options),
            method: 'post',
        })

    customerBagasiSave.form = customerBagasiSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3930
 * @route '/api/admin/customer-bagasi/{id}'
 */
export const customerBagasiDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customerBagasiDelete.url(args, options),
    method: 'delete',
})

customerBagasiDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/customer-bagasi/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3930
 * @route '/api/admin/customer-bagasi/{id}'
 */
customerBagasiDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return customerBagasiDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3930
 * @route '/api/admin/customer-bagasi/{id}'
 */
customerBagasiDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customerBagasiDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3930
 * @route '/api/admin/customer-bagasi/{id}'
 */
    const customerBagasiDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customerBagasiDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerBagasiDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3930
 * @route '/api/admin/customer-bagasi/{id}'
 */
        customerBagasiDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customerBagasiDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    customerBagasiDelete.form = customerBagasiDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
export const customerCharterIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerCharterIndex.url(options),
    method: 'get',
})

customerCharterIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/customer-charter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
customerCharterIndex.url = (options?: RouteQueryOptions) => {
    return customerCharterIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
customerCharterIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerCharterIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
customerCharterIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customerCharterIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
    const customerCharterIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customerCharterIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
        customerCharterIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerCharterIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3944
 * @route '/api/admin/customer-charter'
 */
        customerCharterIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerCharterIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    customerCharterIndex.form = customerCharterIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3991
 * @route '/api/admin/customer-charter'
 */
export const customerCharterSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customerCharterSave.url(options),
    method: 'post',
})

customerCharterSave.definition = {
    methods: ["post"],
    url: '/api/admin/customer-charter',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3991
 * @route '/api/admin/customer-charter'
 */
customerCharterSave.url = (options?: RouteQueryOptions) => {
    return customerCharterSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3991
 * @route '/api/admin/customer-charter'
 */
customerCharterSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customerCharterSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3991
 * @route '/api/admin/customer-charter'
 */
    const customerCharterSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customerCharterSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3991
 * @route '/api/admin/customer-charter'
 */
        customerCharterSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customerCharterSave.url(options),
            method: 'post',
        })

    customerCharterSave.form = customerCharterSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4048
 * @route '/api/admin/customer-charter/{id}'
 */
export const customerCharterDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customerCharterDelete.url(args, options),
    method: 'delete',
})

customerCharterDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/customer-charter/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4048
 * @route '/api/admin/customer-charter/{id}'
 */
customerCharterDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return customerCharterDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4048
 * @route '/api/admin/customer-charter/{id}'
 */
customerCharterDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: customerCharterDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4048
 * @route '/api/admin/customer-charter/{id}'
 */
    const customerCharterDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customerCharterDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::customerCharterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4048
 * @route '/api/admin/customer-charter/{id}'
 */
        customerCharterDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customerCharterDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    customerCharterDelete.form = customerCharterDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
export const charterRoutesMasterIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charterRoutesMasterIndex.url(options),
    method: 'get',
})

charterRoutesMasterIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/charter-routes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
charterRoutesMasterIndex.url = (options?: RouteQueryOptions) => {
    return charterRoutesMasterIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
charterRoutesMasterIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charterRoutesMasterIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
charterRoutesMasterIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: charterRoutesMasterIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
    const charterRoutesMasterIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: charterRoutesMasterIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
        charterRoutesMasterIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charterRoutesMasterIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4062
 * @route '/api/admin/charter-routes'
 */
        charterRoutesMasterIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charterRoutesMasterIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    charterRoutesMasterIndex.form = charterRoutesMasterIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4114
 * @route '/api/admin/charter-routes'
 */
export const charterRoutesMasterSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: charterRoutesMasterSave.url(options),
    method: 'post',
})

charterRoutesMasterSave.definition = {
    methods: ["post"],
    url: '/api/admin/charter-routes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4114
 * @route '/api/admin/charter-routes'
 */
charterRoutesMasterSave.url = (options?: RouteQueryOptions) => {
    return charterRoutesMasterSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4114
 * @route '/api/admin/charter-routes'
 */
charterRoutesMasterSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: charterRoutesMasterSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4114
 * @route '/api/admin/charter-routes'
 */
    const charterRoutesMasterSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: charterRoutesMasterSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4114
 * @route '/api/admin/charter-routes'
 */
        charterRoutesMasterSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: charterRoutesMasterSave.url(options),
            method: 'post',
        })

    charterRoutesMasterSave.form = charterRoutesMasterSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4162
 * @route '/api/admin/charter-routes/{id}'
 */
export const charterRoutesMasterDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: charterRoutesMasterDelete.url(args, options),
    method: 'delete',
})

charterRoutesMasterDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/charter-routes/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4162
 * @route '/api/admin/charter-routes/{id}'
 */
charterRoutesMasterDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return charterRoutesMasterDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4162
 * @route '/api/admin/charter-routes/{id}'
 */
charterRoutesMasterDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: charterRoutesMasterDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4162
 * @route '/api/admin/charter-routes/{id}'
 */
    const charterRoutesMasterDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: charterRoutesMasterDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::charterRoutesMasterDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4162
 * @route '/api/admin/charter-routes/{id}'
 */
        charterRoutesMasterDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: charterRoutesMasterDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    charterRoutesMasterDelete.form = charterRoutesMasterDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
export const unitsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unitsIndex.url(options),
    method: 'get',
})

unitsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/units',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
unitsIndex.url = (options?: RouteQueryOptions) => {
    return unitsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
unitsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unitsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
unitsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: unitsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
    const unitsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: unitsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
        unitsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: unitsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4176
 * @route '/api/admin/units'
 */
        unitsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: unitsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    unitsIndex.form = unitsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4222
 * @route '/api/admin/units'
 */
export const unitsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unitsSave.url(options),
    method: 'post',
})

unitsSave.definition = {
    methods: ["post"],
    url: '/api/admin/units',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4222
 * @route '/api/admin/units'
 */
unitsSave.url = (options?: RouteQueryOptions) => {
    return unitsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4222
 * @route '/api/admin/units'
 */
unitsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unitsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4222
 * @route '/api/admin/units'
 */
    const unitsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unitsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4222
 * @route '/api/admin/units'
 */
        unitsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unitsSave.url(options),
            method: 'post',
        })

    unitsSave.form = unitsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4365
 * @route '/api/admin/units/{id}'
 */
export const unitsDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: unitsDelete.url(args, options),
    method: 'delete',
})

unitsDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/units/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4365
 * @route '/api/admin/units/{id}'
 */
unitsDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return unitsDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4365
 * @route '/api/admin/units/{id}'
 */
unitsDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: unitsDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4365
 * @route '/api/admin/units/{id}'
 */
    const unitsDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unitsDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unitsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4365
 * @route '/api/admin/units/{id}'
 */
        unitsDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unitsDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    unitsDelete.form = unitsDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
export const armadaCategoriesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadaCategoriesIndex.url(options),
    method: 'get',
})

armadaCategoriesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/armada-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
armadaCategoriesIndex.url = (options?: RouteQueryOptions) => {
    return armadaCategoriesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
armadaCategoriesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadaCategoriesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
armadaCategoriesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadaCategoriesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
    const armadaCategoriesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadaCategoriesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
        armadaCategoriesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadaCategoriesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadaCategoriesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4386
 * @route '/api/admin/armada-categories'
 */
        armadaCategoriesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadaCategoriesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    armadaCategoriesIndex.form = armadaCategoriesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
export const armadasIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasIndex.url(options),
    method: 'get',
})

armadasIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/armadas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
armadasIndex.url = (options?: RouteQueryOptions) => {
    return armadasIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
armadasIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
armadasIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadasIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
    const armadasIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadasIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
        armadasIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4412
 * @route '/api/admin/armadas'
 */
        armadasIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    armadasIndex.form = armadasIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
export const armadasShow = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasShow.url(args, options),
    method: 'get',
})

armadasShow.definition = {
    methods: ["get","head"],
    url: '/api/admin/armadas/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
armadasShow.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return armadasShow.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
armadasShow.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadasShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
armadasShow.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadasShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
    const armadasShowForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadasShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
        armadasShowForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasShow
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4889
 * @route '/api/admin/armadas/{id}'
 */
        armadasShowForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadasShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    armadasShow.form = armadasShowForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4716
 * @route '/api/admin/armadas'
 */
export const armadasSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: armadasSave.url(options),
    method: 'post',
})

armadasSave.definition = {
    methods: ["post"],
    url: '/api/admin/armadas',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4716
 * @route '/api/admin/armadas'
 */
armadasSave.url = (options?: RouteQueryOptions) => {
    return armadasSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4716
 * @route '/api/admin/armadas'
 */
armadasSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: armadasSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4716
 * @route '/api/admin/armadas'
 */
    const armadasSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: armadasSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4716
 * @route '/api/admin/armadas'
 */
        armadasSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: armadasSave.url(options),
            method: 'post',
        })

    armadasSave.form = armadasSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4841
 * @route '/api/admin/armadas/{id}'
 */
export const armadasDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: armadasDelete.url(args, options),
    method: 'delete',
})

armadasDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/armadas/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4841
 * @route '/api/admin/armadas/{id}'
 */
armadasDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return armadasDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4841
 * @route '/api/admin/armadas/{id}'
 */
armadasDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: armadasDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4841
 * @route '/api/admin/armadas/{id}'
 */
    const armadasDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: armadasDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::armadasDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4841
 * @route '/api/admin/armadas/{id}'
 */
        armadasDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: armadasDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    armadasDelete.form = armadasDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
export const poolsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poolsIndex.url(options),
    method: 'get',
})

poolsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/pools',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
poolsIndex.url = (options?: RouteQueryOptions) => {
    return poolsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
poolsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poolsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
poolsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: poolsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
    const poolsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: poolsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
        poolsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poolsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5042
 * @route '/api/admin/pools'
 */
        poolsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poolsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    poolsIndex.form = poolsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
export const poolsExport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poolsExport.url(options),
    method: 'get',
})

poolsExport.definition = {
    methods: ["get","head"],
    url: '/api/admin/pools/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
poolsExport.url = (options?: RouteQueryOptions) => {
    return poolsExport.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
poolsExport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poolsExport.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
poolsExport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: poolsExport.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
    const poolsExportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: poolsExport.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
        poolsExportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poolsExport.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsExport
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5064
 * @route '/api/admin/pools/export'
 */
        poolsExportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poolsExport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    poolsExport.form = poolsExportForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5589
 * @route '/api/admin/pools'
 */
export const poolsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: poolsSave.url(options),
    method: 'post',
})

poolsSave.definition = {
    methods: ["post"],
    url: '/api/admin/pools',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5589
 * @route '/api/admin/pools'
 */
poolsSave.url = (options?: RouteQueryOptions) => {
    return poolsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5589
 * @route '/api/admin/pools'
 */
poolsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: poolsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5589
 * @route '/api/admin/pools'
 */
    const poolsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: poolsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5589
 * @route '/api/admin/pools'
 */
        poolsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: poolsSave.url(options),
            method: 'post',
        })

    poolsSave.form = poolsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5742
 * @route '/api/admin/pools/{id}'
 */
export const poolsDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: poolsDelete.url(args, options),
    method: 'delete',
})

poolsDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/pools/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5742
 * @route '/api/admin/pools/{id}'
 */
poolsDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return poolsDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5742
 * @route '/api/admin/pools/{id}'
 */
poolsDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: poolsDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5742
 * @route '/api/admin/pools/{id}'
 */
    const poolsDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: poolsDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5742
 * @route '/api/admin/pools/{id}'
 */
        poolsDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: poolsDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    poolsDelete.form = poolsDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
export const tenantSwitch = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tenantSwitch.url(options),
    method: 'post',
})

tenantSwitch.definition = {
    methods: ["post"],
    url: '/api/admin/tenant/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
tenantSwitch.url = (options?: RouteQueryOptions) => {
    return tenantSwitch.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
tenantSwitch.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tenantSwitch.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
    const tenantSwitchForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: tenantSwitch.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
        tenantSwitchForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: tenantSwitch.url(options),
            method: 'post',
        })

    tenantSwitch.form = tenantSwitchForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
export const poolSwitch = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: poolSwitch.url(options),
    method: 'post',
})

poolSwitch.definition = {
    methods: ["post"],
    url: '/api/admin/pool/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
poolSwitch.url = (options?: RouteQueryOptions) => {
    return poolSwitch.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
poolSwitch.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: poolSwitch.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
    const poolSwitchForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: poolSwitch.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::poolSwitch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
        poolSwitchForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: poolSwitch.url(options),
            method: 'post',
        })

    poolSwitch.form = poolSwitchForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
export const usersIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: usersIndex.url(options),
    method: 'get',
})

usersIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
usersIndex.url = (options?: RouteQueryOptions) => {
    return usersIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
usersIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: usersIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
usersIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: usersIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
    const usersIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: usersIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
        usersIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: usersIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
        usersIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: usersIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    usersIndex.form = usersIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
export const usersSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersSave.url(options),
    method: 'post',
})

usersSave.definition = {
    methods: ["post"],
    url: '/api/admin/users',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
usersSave.url = (options?: RouteQueryOptions) => {
    return usersSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
usersSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
    const usersSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: usersSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
        usersSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: usersSave.url(options),
            method: 'post',
        })

    usersSave.form = usersSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersVerify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
export const usersVerify = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersVerify.url(args, options),
    method: 'post',
})

usersVerify.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersVerify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
usersVerify.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return usersVerify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersVerify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
usersVerify.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersVerify.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersVerify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
    const usersVerifyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: usersVerify.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersVerify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
        usersVerifyForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: usersVerify.url(args, options),
            method: 'post',
        })

    usersVerify.form = usersVerifyForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersUnverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
export const usersUnverify = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersUnverify.url(args, options),
    method: 'post',
})

usersUnverify.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/unverify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersUnverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
usersUnverify.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return usersUnverify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersUnverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
usersUnverify.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersUnverify.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersUnverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
    const usersUnverifyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: usersUnverify.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersUnverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
        usersUnverifyForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: usersUnverify.url(args, options),
            method: 'post',
        })

    usersUnverify.form = usersUnverifyForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
export const usersSendVerification = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersSendVerification.url(args, options),
    method: 'post',
})

usersSendVerification.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/send-verification',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
usersSendVerification.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return usersSendVerification.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
usersSendVerification.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: usersSendVerification.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
    const usersSendVerificationForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: usersSendVerification.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersSendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
        usersSendVerificationForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: usersSendVerification.url(args, options),
            method: 'post',
        })

    usersSendVerification.form = usersSendVerificationForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
export const usersDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: usersDelete.url(args, options),
    method: 'delete',
})

usersDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/users/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
usersDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return usersDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
usersDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: usersDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
    const usersDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: usersDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::usersDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
        usersDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: usersDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    usersDelete.form = usersDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
export const rolesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rolesIndex.url(options),
    method: 'get',
})

rolesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
rolesIndex.url = (options?: RouteQueryOptions) => {
    return rolesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
rolesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: rolesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
rolesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: rolesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
    const rolesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: rolesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
        rolesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rolesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5761
 * @route '/api/admin/roles'
 */
        rolesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: rolesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    rolesIndex.form = rolesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5777
 * @route '/api/admin/roles'
 */
export const rolesSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rolesSave.url(options),
    method: 'post',
})

rolesSave.definition = {
    methods: ["post"],
    url: '/api/admin/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5777
 * @route '/api/admin/roles'
 */
rolesSave.url = (options?: RouteQueryOptions) => {
    return rolesSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5777
 * @route '/api/admin/roles'
 */
rolesSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: rolesSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5777
 * @route '/api/admin/roles'
 */
    const rolesSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: rolesSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5777
 * @route '/api/admin/roles'
 */
        rolesSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: rolesSave.url(options),
            method: 'post',
        })

    rolesSave.form = rolesSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5896
 * @route '/api/admin/roles/{id}'
 */
export const rolesDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: rolesDelete.url(args, options),
    method: 'delete',
})

rolesDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/roles/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5896
 * @route '/api/admin/roles/{id}'
 */
rolesDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return rolesDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5896
 * @route '/api/admin/roles/{id}'
 */
rolesDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: rolesDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5896
 * @route '/api/admin/roles/{id}'
 */
    const rolesDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: rolesDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::rolesDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5896
 * @route '/api/admin/roles/{id}'
 */
        rolesDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: rolesDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    rolesDelete.form = rolesDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
export const tenantsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tenantsIndex.url(options),
    method: 'get',
})

tenantsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
tenantsIndex.url = (options?: RouteQueryOptions) => {
    return tenantsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
tenantsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tenantsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
tenantsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tenantsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
    const tenantsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tenantsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
        tenantsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tenantsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:9997
 * @route '/api/admin/tenants'
 */
        tenantsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tenantsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    tenantsIndex.form = tenantsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10100
 * @route '/api/admin/tenants'
 */
export const tenantsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tenantsSave.url(options),
    method: 'post',
})

tenantsSave.definition = {
    methods: ["post"],
    url: '/api/admin/tenants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10100
 * @route '/api/admin/tenants'
 */
tenantsSave.url = (options?: RouteQueryOptions) => {
    return tenantsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10100
 * @route '/api/admin/tenants'
 */
tenantsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tenantsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10100
 * @route '/api/admin/tenants'
 */
    const tenantsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: tenantsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10100
 * @route '/api/admin/tenants'
 */
        tenantsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: tenantsSave.url(options),
            method: 'post',
        })

    tenantsSave.form = tenantsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10175
 * @route '/api/admin/tenants/{id}'
 */
export const tenantsDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: tenantsDelete.url(args, options),
    method: 'delete',
})

tenantsDelete.definition = {
    methods: ["delete"],
    url: '/api/admin/tenants/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10175
 * @route '/api/admin/tenants/{id}'
 */
tenantsDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return tenantsDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10175
 * @route '/api/admin/tenants/{id}'
 */
tenantsDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: tenantsDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10175
 * @route '/api/admin/tenants/{id}'
 */
    const tenantsDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: tenantsDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tenantsDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10175
 * @route '/api/admin/tenants/{id}'
 */
        tenantsDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: tenantsDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })

    tenantsDelete.form = tenantsDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
export const subscriptionsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: subscriptionsIndex.url(options),
    method: 'get',
})

subscriptionsIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/subscriptions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
subscriptionsIndex.url = (options?: RouteQueryOptions) => {
    return subscriptionsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
subscriptionsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: subscriptionsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
subscriptionsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: subscriptionsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
    const subscriptionsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: subscriptionsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
        subscriptionsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: subscriptionsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10217
 * @route '/api/admin/subscriptions'
 */
        subscriptionsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: subscriptionsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    subscriptionsIndex.form = subscriptionsIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10298
 * @route '/api/admin/subscriptions'
 */
export const subscriptionsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: subscriptionsSave.url(options),
    method: 'post',
})

subscriptionsSave.definition = {
    methods: ["post"],
    url: '/api/admin/subscriptions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10298
 * @route '/api/admin/subscriptions'
 */
subscriptionsSave.url = (options?: RouteQueryOptions) => {
    return subscriptionsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10298
 * @route '/api/admin/subscriptions'
 */
subscriptionsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: subscriptionsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10298
 * @route '/api/admin/subscriptions'
 */
    const subscriptionsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: subscriptionsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::subscriptionsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10298
 * @route '/api/admin/subscriptions'
 */
        subscriptionsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: subscriptionsSave.url(options),
            method: 'post',
        })

    subscriptionsSave.form = subscriptionsSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
export const plansIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plansIndex.url(options),
    method: 'get',
})

plansIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
plansIndex.url = (options?: RouteQueryOptions) => {
    return plansIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
plansIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plansIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
plansIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plansIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
    const plansIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: plansIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
        plansIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plansIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10432
 * @route '/api/admin/plans'
 */
        plansIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plansIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    plansIndex.form = plansIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10508
 * @route '/api/admin/plans'
 */
export const plansSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: plansSave.url(options),
    method: 'post',
})

plansSave.definition = {
    methods: ["post"],
    url: '/api/admin/plans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10508
 * @route '/api/admin/plans'
 */
plansSave.url = (options?: RouteQueryOptions) => {
    return plansSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10508
 * @route '/api/admin/plans'
 */
plansSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: plansSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10508
 * @route '/api/admin/plans'
 */
    const plansSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: plansSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::plansSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10508
 * @route '/api/admin/plans'
 */
        plansSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: plansSave.url(options),
            method: 'post',
        })

    plansSave.form = plansSaveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
export const invoicesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})

invoicesIndex.definition = {
    methods: ["get","head"],
    url: '/api/admin/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
invoicesIndex.url = (options?: RouteQueryOptions) => {
    return invoicesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
invoicesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
invoicesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoicesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
    const invoicesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoicesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
        invoicesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesIndex
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10593
 * @route '/api/admin/invoices'
 */
        invoicesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    invoicesIndex.form = invoicesIndexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10736
 * @route '/api/admin/invoices/{id}/mark-paid'
 */
export const invoicesMarkPaid = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesMarkPaid.url(args, options),
    method: 'post',
})

invoicesMarkPaid.definition = {
    methods: ["post"],
    url: '/api/admin/invoices/{id}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10736
 * @route '/api/admin/invoices/{id}/mark-paid'
 */
invoicesMarkPaid.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }


    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return invoicesMarkPaid.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10736
 * @route '/api/admin/invoices/{id}/mark-paid'
 */
invoicesMarkPaid.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesMarkPaid.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10736
 * @route '/api/admin/invoices/{id}/mark-paid'
 */
    const invoicesMarkPaidForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invoicesMarkPaid.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::invoicesMarkPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10736
 * @route '/api/admin/invoices/{id}/mark-paid'
 */
        invoicesMarkPaidForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invoicesMarkPaid.url(args, options),
            method: 'post',
        })

    invoicesMarkPaid.form = invoicesMarkPaidForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
export const paymentSettingsGet = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentSettingsGet.url(options),
    method: 'get',
})

paymentSettingsGet.definition = {
    methods: ["get","head"],
    url: '/api/admin/payment-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
paymentSettingsGet.url = (options?: RouteQueryOptions) => {
    return paymentSettingsGet.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
paymentSettingsGet.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentSettingsGet.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
paymentSettingsGet.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: paymentSettingsGet.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
    const paymentSettingsGetForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: paymentSettingsGet.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
        paymentSettingsGetForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentSettingsGet.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsGet
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10764
 * @route '/api/admin/payment-settings'
 */
        paymentSettingsGetForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentSettingsGet.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    paymentSettingsGet.form = paymentSettingsGetForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10771
 * @route '/api/admin/payment-settings'
 */
export const paymentSettingsSave = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentSettingsSave.url(options),
    method: 'post',
})

paymentSettingsSave.definition = {
    methods: ["post"],
    url: '/api/admin/payment-settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10771
 * @route '/api/admin/payment-settings'
 */
paymentSettingsSave.url = (options?: RouteQueryOptions) => {
    return paymentSettingsSave.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10771
 * @route '/api/admin/payment-settings'
 */
paymentSettingsSave.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentSettingsSave.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10771
 * @route '/api/admin/payment-settings'
 */
    const paymentSettingsSaveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: paymentSettingsSave.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::paymentSettingsSave
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10771
 * @route '/api/admin/payment-settings'
 */
        paymentSettingsSaveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: paymentSettingsSave.url(options),
            method: 'post',
        })

    paymentSettingsSave.form = paymentSettingsSaveForm
const AdminOpsApiController = { reportsSummary, reportsBookingsCsv, reportsRevenueCsv, routesIndex, routesSave, routesDelete, schedulesIndex, schedulesSave, schedulesDelete, driversIndex, driversSave, driversDelete, luggageServicesIndex, luggageServicesSave, luggageServicesDelete, segmentsIndex, segmentsSave, segmentsDelete, customersTemplate, customersImport, customersIndex, customersSave, customersDelete, activityLogsIndex, armadasExport, driversExport, chartersIndex, chartersShow, chartersSave, chartersBulkDelete, chartersMarkBopDone, chartersMarkPaid, chartersMarkDone, chartersDelete, luggagesIndex, luggagesSave, luggagesBulkDelete, luggagesBulkStatus, luggagesMarkPaid, luggagesMarkActive, luggagesMarkDone, luggagesMarkCanceled, luggagesTracking, luggagesTrackingAdd, luggagesDelete, assignmentsIndex, assignmentsConflicts, assignmentsSave, assignmentsBulkDelete, assignmentsDelete, customerBagasiIndex, customerBagasiSave, customerBagasiDelete, customerCharterIndex, customerCharterSave, customerCharterDelete, charterRoutesMasterIndex, charterRoutesMasterSave, charterRoutesMasterDelete, unitsIndex, unitsSave, unitsDelete, armadaCategoriesIndex, armadasIndex, armadasShow, armadasSave, armadasDelete, poolsIndex, poolsExport, poolsSave, poolsDelete, tenantSwitch, poolSwitch, usersIndex, usersSave, usersVerify, usersUnverify, usersSendVerification, usersDelete, rolesIndex, rolesSave, rolesDelete, tenantsIndex, tenantsSave, tenantsDelete, subscriptionsIndex, subscriptionsSave, plansIndex, plansSave, invoicesIndex, invoicesMarkPaid, paymentSettingsGet, paymentSettingsSave }

export default AdminOpsApiController
