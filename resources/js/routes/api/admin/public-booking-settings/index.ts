import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from '../../../../wayfinder'
/**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/public-booking-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingAdminController::index
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
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
* @see \App\Http\Controllers\PublicBookingAdminController::update
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/api/admin/public-booking-settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::update
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::update
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::update
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::update
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
const publicBookingSettings = {
    index: Object.assign(index, index),
update: Object.assign(update, update),
}

export default publicBookingSettings