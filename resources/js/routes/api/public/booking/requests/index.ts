import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::store
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:33
 * @route '/api/public/booking/{tenantSlug}/requests'
 */
export const store = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/public/booking/{tenantSlug}/requests',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::store
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:33
 * @route '/api/public/booking/{tenantSlug}/requests'
 */
store.url = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{tenantSlug}', parsedArgs.tenantSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::store
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:33
 * @route '/api/public/booking/{tenantSlug}/requests'
 */
store.post = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\PublicBookingApiController::store
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:33
 * @route '/api/public/booking/{tenantSlug}/requests'
 */
    const storeForm = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\PublicBookingApiController::store
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:33
 * @route '/api/public/booking/{tenantSlug}/requests'
 */
        storeForm.post = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
const requests = {
    store: Object.assign(store, store),
}

export default requests