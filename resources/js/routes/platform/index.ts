import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from '../../wayfinder'
import saasB7f852 from './saas'
/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/platform/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlatformDashboardController::__invoke
 * @see app/Http/Controllers/PlatformDashboardController.php:19
 * @route '/platform/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
export const saas = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: saas.url(options),
    method: 'get',
})

saas.definition = {
    methods: ["get","head"],
    url: '/platform/saas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
saas.url = (options?: RouteQueryOptions) => {
    return saas.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
saas.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: saas.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
saas.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: saas.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
    const saasForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: saas.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
        saasForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: saas.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
        saasForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: saas.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    saas.form = saasForm
const platform = {
    dashboard: Object.assign(dashboard, dashboard),
saas: Object.assign(saas, saasB7f852),
}

export default platform