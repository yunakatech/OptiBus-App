import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
const PlatformDashboardController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PlatformDashboardController.url(options),
    method: 'get',
})

PlatformDashboardController.definition = {
    methods: ["get","head"],
    url: '/platform/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
PlatformDashboardController.url = (options?: RouteQueryOptions) => {
    return PlatformDashboardController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
PlatformDashboardController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PlatformDashboardController.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
PlatformDashboardController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: PlatformDashboardController.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
    const PlatformDashboardControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: PlatformDashboardController.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
        PlatformDashboardControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PlatformDashboardController.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:16
 * @route '/platform/dashboard'
 */
        PlatformDashboardControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PlatformDashboardController.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    PlatformDashboardController.form = PlatformDashboardControllerForm
export default PlatformDashboardController