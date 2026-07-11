import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import manifest from './manifest'
import ticket from './ticket'
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/bookings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
export const detail = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})

detail.definition = {
    methods: ["get","head"],
    url: '/bookings/detail/{groupKey}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
detail.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { groupKey: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    groupKey: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        groupKey: args.groupKey,
                }

    return detail.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
detail.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: detail.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
detail.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: detail.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
    const detailForm = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: detail.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
        detailForm.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: detail.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
        detailForm.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: detail.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    detail.form = detailForm
const bookings = {
    index: Object.assign(index, index),
detail: Object.assign(detail, detail),
manifest: Object.assign(manifest, manifest),
ticket: Object.assign(ticket, ticket),
}

export default bookings