import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
export const inbox = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inbox.url(options),
    method: 'get',
})

inbox.definition = {
    methods: ["get","head"],
    url: '/booking-requests',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
inbox.url = (options?: RouteQueryOptions) => {
    return inbox.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
inbox.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inbox.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
inbox.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inbox.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
    const inboxForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inbox.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
        inboxForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inbox.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingAdminController::inbox
 * @see app/Http/Controllers/PublicBookingAdminController.php:22
 * @route '/booking-requests'
 */
        inboxForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inbox.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    inbox.form = inboxForm
/**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/settings/booking-online',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settings.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
        settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingAdminController::settings
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
        settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
export const settingsData = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settingsData.url(options),
    method: 'get',
})

settingsData.definition = {
    methods: ["get","head"],
    url: '/api/admin/public-booking-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
settingsData.url = (options?: RouteQueryOptions) => {
    return settingsData.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
settingsData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settingsData.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
settingsData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settingsData.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
    const settingsDataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settingsData.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
        settingsDataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settingsData.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingAdminController::settingsData
 * @see app/Http/Controllers/PublicBookingAdminController.php:27
 * @route '/api/admin/public-booking-settings'
 */
        settingsDataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settingsData.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settingsData.form = settingsDataForm
/**
* @see \App\Http\Controllers\PublicBookingAdminController::updateSettings
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
export const updateSettings = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateSettings.url(options),
    method: 'post',
})

updateSettings.definition = {
    methods: ["post"],
    url: '/api/admin/public-booking-settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::updateSettings
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
updateSettings.url = (options?: RouteQueryOptions) => {
    return updateSettings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::updateSettings
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
updateSettings.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateSettings.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::updateSettings
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
    const updateSettingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateSettings.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::updateSettings
 * @see app/Http/Controllers/PublicBookingAdminController.php:32
 * @route '/api/admin/public-booking-settings'
 */
        updateSettingsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateSettings.url(options),
            method: 'post',
        })
    
    updateSettings.form = updateSettingsForm
const PublicBookingAdminController = { inbox, settings, settingsData, updateSettings }

export default PublicBookingAdminController