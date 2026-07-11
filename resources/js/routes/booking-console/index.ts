import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/booking-console',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
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
const bookingConsole = {
    index: Object.assign(index, index),
}

export default bookingConsole