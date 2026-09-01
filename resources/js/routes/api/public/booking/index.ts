import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../wayfinder'
import requests from './requests'
/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
export const availability = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: availability.url(args, options),
    method: 'get',
})

availability.definition = {
    methods: ["get","head"],
    url: '/api/public/booking/{tenantSlug}/availability',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
availability.url = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return availability.definition.url
            .replace('{tenantSlug}', parsedArgs.tenantSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
availability.get = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: availability.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
availability.head = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: availability.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
    const availabilityForm = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: availability.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
        availabilityForm.get = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: availability.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\PublicBookingApiController::availability
 * @see app/Http/Controllers/Api/PublicBookingApiController.php:14
 * @route '/api/public/booking/{tenantSlug}/availability'
 */
        availabilityForm.head = (args: { tenantSlug: string | number } | [tenantSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: availability.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    availability.form = availabilityForm
const booking = {
    availability: Object.assign(availability, availability),
requests: Object.assign(requests, requests),
}

export default booking