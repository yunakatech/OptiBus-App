import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
export const style = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: style.url(options),
    method: 'get',
})

style.definition = {
    methods: ["get","head"],
    url: '/style.css',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
style.url = (options?: RouteQueryOptions) => {
    return style.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
style.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: style.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
style.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: style.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
    const styleForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: style.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
        styleForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: style.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StaticAssetController::style
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
        styleForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: style.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    style.form = styleForm
const StaticAssetController = { style }

export default StaticAssetController