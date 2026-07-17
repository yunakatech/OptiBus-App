import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import webhooks from './webhooks'
import build from './build'
import bookings from './bookings'
import master from './master'
import ops from './ops'
import admin from './admin'
/**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
export const plans = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

plans.definition = {
    methods: ["get","head"],
    url: '/api/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
plans.url = (options?: RouteQueryOptions) => {
    return plans.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
plans.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
plans.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plans.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
    const plansForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: plans.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
        plansForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\PublicApiController::plans
 * @see app/Http/Controllers/Api/PublicApiController.php:16
 * @route '/api/plans'
 */
        plansForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    plans.form = plansForm
const api = {
    plans: Object.assign(plans, plans),
webhooks: Object.assign(webhooks, webhooks),
build: Object.assign(build, build),
bookings: Object.assign(bookings, bookings),
master: Object.assign(master, master),
ops: Object.assign(ops, ops),
admin: Object.assign(admin, admin),
}

export default api