import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
export const get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: get.url(options),
    method: 'get',
})

get.definition = {
    methods: ["get","head"],
    url: '/api/admin/payment-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
get.url = (options?: RouteQueryOptions) => {
    return get.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
get.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: get.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
get.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: get.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
    const getForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: get.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
        getForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: get.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::get
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10453
 * @route '/api/admin/payment-settings'
 */
        getForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: get.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    get.form = getForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10460
 * @route '/api/admin/payment-settings'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/api/admin/payment-settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10460
 * @route '/api/admin/payment-settings'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10460
 * @route '/api/admin/payment-settings'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10460
 * @route '/api/admin/payment-settings'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:10460
 * @route '/api/admin/payment-settings'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
const paymentSettings = {
    get: Object.assign(get, get),
save: Object.assign(save, save),
}

export default paymentSettings