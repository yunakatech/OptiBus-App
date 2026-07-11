import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\BookingApiController::map
 * @see app/Http/Controllers/Api/BookingApiController.php:1311
 * @route '/api/bookings/departure-riturs/map'
 */
export const map = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: map.url(options),
    method: 'post',
})

map.definition = {
    methods: ["post"],
    url: '/api/bookings/departure-riturs/map',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::map
 * @see app/Http/Controllers/Api/BookingApiController.php:1311
 * @route '/api/bookings/departure-riturs/map'
 */
map.url = (options?: RouteQueryOptions) => {
    return map.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::map
 * @see app/Http/Controllers/Api/BookingApiController.php:1311
 * @route '/api/bookings/departure-riturs/map'
 */
map.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: map.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::map
 * @see app/Http/Controllers/Api/BookingApiController.php:1311
 * @route '/api/bookings/departure-riturs/map'
 */
    const mapForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: map.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::map
 * @see app/Http/Controllers/Api/BookingApiController.php:1311
 * @route '/api/bookings/departure-riturs/map'
 */
        mapForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: map.url(options),
            method: 'post',
        })
    
    map.form = mapForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::unmap
 * @see app/Http/Controllers/Api/BookingApiController.php:1402
 * @route '/api/bookings/departure-riturs/unmap'
 */
export const unmap = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unmap.url(options),
    method: 'post',
})

unmap.definition = {
    methods: ["post"],
    url: '/api/bookings/departure-riturs/unmap',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::unmap
 * @see app/Http/Controllers/Api/BookingApiController.php:1402
 * @route '/api/bookings/departure-riturs/unmap'
 */
unmap.url = (options?: RouteQueryOptions) => {
    return unmap.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::unmap
 * @see app/Http/Controllers/Api/BookingApiController.php:1402
 * @route '/api/bookings/departure-riturs/unmap'
 */
unmap.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unmap.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::unmap
 * @see app/Http/Controllers/Api/BookingApiController.php:1402
 * @route '/api/bookings/departure-riturs/unmap'
 */
    const unmapForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unmap.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::unmap
 * @see app/Http/Controllers/Api/BookingApiController.php:1402
 * @route '/api/bookings/departure-riturs/unmap'
 */
        unmapForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unmap.url(options),
            method: 'post',
        })
    
    unmap.form = unmapForm
const departureRiturs = {
    map: Object.assign(map, map),
unmap: Object.assign(unmap, unmap),
}

export default departureRiturs