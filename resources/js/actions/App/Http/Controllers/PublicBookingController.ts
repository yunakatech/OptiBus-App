import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
export const show = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/book/{tenantSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
show.url = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenantSlug: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    tenantSlug: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        tenantSlug: args.tenantSlug,
                }

    return show.definition.url
            .replace('{tenantSlug}', parsedArgs.tenantSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
show.get = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
show.head = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
    const showForm = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
        showForm.get = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingController::show
 * @see app/Http/Controllers/PublicBookingController.php:13
 * @route '/book/{tenantSlug}'
 */
        showForm.head = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const PublicBookingController = { show }

export default PublicBookingController