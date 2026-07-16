import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
export const switchMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(options),
    method: 'post',
})

switchMethod.definition = {
    methods: ["post"],
    url: '/api/admin/pool/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
switchMethod.url = (options?: RouteQueryOptions) => {
    return switchMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
switchMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: switchMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
    const switchMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: switchMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::switchMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4930
 * @route '/api/admin/pool/switch'
 */
        switchMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: switchMethod.url(options),
            method: 'post',
        })
    
    switchMethod.form = switchMethodForm
const pool = {
    switch: Object.assign(switchMethod, switchMethod),
}

export default pool