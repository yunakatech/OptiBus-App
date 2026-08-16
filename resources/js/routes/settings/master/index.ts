import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from '../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
export const customerBagasi = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerBagasi.url(options),
    method: 'get',
})

customerBagasi.definition = {
    methods: ["get","head"],
    url: '/settings/customer-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
customerBagasi.url = (options?: RouteQueryOptions) => {
    return customerBagasi.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
customerBagasi.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerBagasi.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
customerBagasi.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customerBagasi.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
    const customerBagasiForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customerBagasi.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
        customerBagasiForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerBagasi.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
        customerBagasiForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerBagasi.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customerBagasi.form = customerBagasiForm
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
export const customerCharter = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerCharter.url(options),
    method: 'get',
})

customerCharter.definition = {
    methods: ["get","head"],
    url: '/settings/customer-charter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
customerCharter.url = (options?: RouteQueryOptions) => {
    return customerCharter.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
customerCharter.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerCharter.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
customerCharter.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customerCharter.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
    const customerCharterForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customerCharter.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
        customerCharterForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerCharter.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
        customerCharterForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerCharter.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customerCharter.form = customerCharterForm
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
export const ruteCarter = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ruteCarter.url(options),
    method: 'get',
})

ruteCarter.definition = {
    methods: ["get","head"],
    url: '/settings/rute-carter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
ruteCarter.url = (options?: RouteQueryOptions) => {
    return ruteCarter.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
ruteCarter.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ruteCarter.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
ruteCarter.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ruteCarter.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
    const ruteCarterForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: ruteCarter.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
        ruteCarterForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ruteCarter.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
        ruteCarterForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ruteCarter.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    ruteCarter.form = ruteCarterForm
const master = {
    customerBagasi: Object.assign(customerBagasi, customerBagasi),
customerCharter: Object.assign(customerCharter, customerCharter),
ruteCarter: Object.assign(ruteCarter, ruteCarter),
}

export default master