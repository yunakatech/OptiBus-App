import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
export const layout = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: layout.url(args, options),
    method: 'get',
})

layout.definition = {
    methods: ["get","head"],
    url: '/admin-ops/kategori-armada/layout/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
layout.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return layout.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
layout.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: layout.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
layout.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: layout.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
    const layoutForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: layout.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
        layoutForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: layout.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:28
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
        layoutForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: layout.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    layout.form = layoutForm
const units = {
    layout: Object.assign(layout, layout),
}

export default units