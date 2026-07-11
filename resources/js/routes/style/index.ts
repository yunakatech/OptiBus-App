import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
export const css = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: css.url(options),
    method: 'get',
})

css.definition = {
    methods: ["get","head"],
    url: '/style.css',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
css.url = (options?: RouteQueryOptions) => {
    return css.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
css.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: css.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
css.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: css.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
    const cssForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: css.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
        cssForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: css.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StaticAssetController::css
 * @see app/Http/Controllers/StaticAssetController.php:10
 * @route '/style.css'
 */
        cssForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: css.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    css.form = cssForm
const style = {
    css: Object.assign(css, css),
}

export default style