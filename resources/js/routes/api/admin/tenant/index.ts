import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
export const switchMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(options),
    method: 'post',
})

switchMethod.definition = {
    methods: ["post"],
    url: '/api/admin/tenant/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
switchMethod.url = (options?: RouteQueryOptions) => {
    return switchMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
switchMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
    const switchMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: switchMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4978
 * @route '/api/admin/tenant/switch'
 */
        switchMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: switchMethod.url(options),
            method: 'post',
        })
    
    switchMethod.form = switchMethodForm
const tenant = {
    switch: Object.assign(switchMethod, switchMethod),
}

export default tenant