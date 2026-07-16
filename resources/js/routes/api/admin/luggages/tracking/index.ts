import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::add
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
export const add = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

add.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/tracking',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::add
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
add.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return add.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::add
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
add.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::add
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
    const addForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: add.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::add
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3505
 * @route '/api/admin/luggages/{id}/tracking'
 */
        addForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: add.url(args, options),
            method: 'post',
        })
    
    add.form = addForm
const tracking = {
    add: Object.assign(add, add),
}

export default tracking