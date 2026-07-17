import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
export const redirect = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(args, options),
    method: 'get',
})

redirect.definition = {
    methods: ["get","head"],
    url: '/api/build/{path}',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
redirect.url = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { path: args }
    }


    if (Array.isArray(args)) {
        args = {
                    path: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        path: args.path,
                }

    return redirect.definition.url
            .replace('{path}', parsedArgs.path.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
redirect.get = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(args, options),
    method: 'get',
})
/**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
redirect.head = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: redirect.url(args, options),
    method: 'head',
})

    /**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
    const redirectForm = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: redirect.url(args, options),
        method: 'get',
    })

            /**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
        redirectForm.get = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: redirect.url(args, options),
            method: 'get',
        })
            /**
 * @see routes/web.php:43
 * @route '/api/build/{path}'
 */
        redirectForm.head = (args: { path: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: redirect.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    redirect.form = redirectForm
const build = {
    redirect: Object.assign(redirect, redirect),
}

export default build
