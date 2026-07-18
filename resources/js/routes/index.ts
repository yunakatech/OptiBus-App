import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(options),
        method: 'get',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
        loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(options),
            method: 'get',
        })
            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
 * @route '/login'
 */
        loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
 * @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
 * @route '/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::register
 * @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:41
 * @route '/register'
 */
        registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
export const onboarding = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})

onboarding.definition = {
    methods: ["get","head"],
    url: '/onboarding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
onboarding.url = (options?: RouteQueryOptions) => {
    return onboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
onboarding.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
onboarding.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: onboarding.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
    const onboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: onboarding.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
        onboardingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: onboarding.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\OnboardingController::onboarding
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
        onboardingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: onboarding.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    onboarding.form = onboardingForm
/**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
    const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: home.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
        homeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicController::home
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
        homeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    home.form = homeForm
/**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
export const pricing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing.url(options),
    method: 'get',
})

pricing.definition = {
    methods: ["get","head"],
    url: '/pricing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
pricing.url = (options?: RouteQueryOptions) => {
    return pricing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
pricing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
pricing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pricing.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
    const pricingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pricing.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
        pricingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricing.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicController::pricing
 * @see app/Http/Controllers/PublicController.php:28
 * @route '/pricing'
 */
        pricingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricing.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pricing.form = pricingForm
/**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DashboardController::__invoke
 * @see app/Http/Controllers/DashboardController.php:19
 * @route '/dashboard'
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
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
export const adminLuggageServices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminLuggageServices.url(options),
    method: 'get',
})

adminLuggageServices.definition = {
    methods: ["get","head"],
    url: '/admin/luggage-services',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
adminLuggageServices.url = (options?: RouteQueryOptions) => {
    return adminLuggageServices.definition.url + queryParams(options)
}

/**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
adminLuggageServices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminLuggageServices.url(options),
    method: 'get',
})
/**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
adminLuggageServices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: adminLuggageServices.url(options),
    method: 'head',
})

    /**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
    const adminLuggageServicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: adminLuggageServices.url(options),
        method: 'get',
    })

            /**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
        adminLuggageServicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminLuggageServices.url(options),
            method: 'get',
        })
            /**
 * @see [serialized-closure]:2
 * @route '/admin/luggage-services'
 */
        adminLuggageServicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminLuggageServices.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    adminLuggageServices.form = adminLuggageServicesForm
/**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
export const admin_routes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_routes.url(options),
    method: 'get',
})

admin_routes.definition = {
    methods: ["get","head"],
    url: '/admin/routes',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
admin_routes.url = (options?: RouteQueryOptions) => {
    return admin_routes.definition.url + queryParams(options)
}

/**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
admin_routes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_routes.url(options),
    method: 'get',
})
/**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
admin_routes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: admin_routes.url(options),
    method: 'head',
})

    /**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
    const admin_routesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: admin_routes.url(options),
        method: 'get',
    })

            /**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
        admin_routesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_routes.url(options),
            method: 'get',
        })
            /**
 * @see [serialized-closure]:2
 * @route '/admin/routes'
 */
        admin_routesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_routes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    admin_routes.form = admin_routesForm
/**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
export const admin_schedules = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_schedules.url(options),
    method: 'get',
})

admin_schedules.definition = {
    methods: ["get","head"],
    url: '/admin/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
admin_schedules.url = (options?: RouteQueryOptions) => {
    return admin_schedules.definition.url + queryParams(options)
}

/**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
admin_schedules.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_schedules.url(options),
    method: 'get',
})
/**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
admin_schedules.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: admin_schedules.url(options),
    method: 'head',
})

    /**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
    const admin_schedulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: admin_schedules.url(options),
        method: 'get',
    })

            /**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
        admin_schedulesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_schedules.url(options),
            method: 'get',
        })
            /**
 * @see [serialized-closure]:2
 * @route '/admin/schedules'
 */
        admin_schedulesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_schedules.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    admin_schedules.form = admin_schedulesForm
/**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
export const admin_segments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_segments.url(options),
    method: 'get',
})

admin_segments.definition = {
    methods: ["get","head"],
    url: '/admin/segments',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
admin_segments.url = (options?: RouteQueryOptions) => {
    return admin_segments.definition.url + queryParams(options)
}

/**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
admin_segments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_segments.url(options),
    method: 'get',
})
/**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
admin_segments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: admin_segments.url(options),
    method: 'head',
})

    /**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
    const admin_segmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: admin_segments.url(options),
        method: 'get',
    })

            /**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
        admin_segmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_segments.url(options),
            method: 'get',
        })
            /**
 * @see [serialized-closure]:2
 * @route '/admin/segments'
 */
        admin_segmentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_segments.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    admin_segments.form = admin_segmentsForm
/**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
export const admin_units = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_units.url(options),
    method: 'get',
})

admin_units.definition = {
    methods: ["get","head"],
    url: '/admin/units',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
admin_units.url = (options?: RouteQueryOptions) => {
    return admin_units.definition.url + queryParams(options)
}

/**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
admin_units.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admin_units.url(options),
    method: 'get',
})
/**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
admin_units.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: admin_units.url(options),
    method: 'head',
})

    /**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
    const admin_unitsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: admin_units.url(options),
        method: 'get',
    })

            /**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
        admin_unitsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_units.url(options),
            method: 'get',
        })
            /**
 * @see [serialized-closure]:2
 * @route '/admin/units'
 */
        admin_unitsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admin_units.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    admin_units.form = admin_unitsForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
export const adminArmadaCategories = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminArmadaCategories.url(options),
    method: 'get',
})

adminArmadaCategories.definition = {
    methods: ["get","head"],
    url: '/admin/armada-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
adminArmadaCategories.url = (options?: RouteQueryOptions) => {
    return adminArmadaCategories.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
adminArmadaCategories.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminArmadaCategories.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
adminArmadaCategories.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: adminArmadaCategories.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
    const adminArmadaCategoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: adminArmadaCategories.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
        adminArmadaCategoriesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminArmadaCategories.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminArmadaCategories
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4396
 * @route '/admin/armada-categories'
 */
        adminArmadaCategoriesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminArmadaCategories.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    adminArmadaCategories.form = adminArmadaCategoriesForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
export const adminActivityLogs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminActivityLogs.url(options),
    method: 'get',
})

adminActivityLogs.definition = {
    methods: ["get","head"],
    url: '/admin/activity-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
adminActivityLogs.url = (options?: RouteQueryOptions) => {
    return adminActivityLogs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
adminActivityLogs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: adminActivityLogs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
adminActivityLogs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: adminActivityLogs.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
    const adminActivityLogsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: adminActivityLogs.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
        adminActivityLogsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminActivityLogs.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::adminActivityLogs
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:1363
 * @route '/admin/activity-logs'
 */
        adminActivityLogsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: adminActivityLogs.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    adminActivityLogs.form = adminActivityLogsForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_tenant_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4988
 * @route '/admin/tenant/switch'
 */
export const admin_tenant_switch = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: admin_tenant_switch.url(options),
    method: 'post',
})

admin_tenant_switch.definition = {
    methods: ["post"],
    url: '/admin/tenant/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_tenant_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4988
 * @route '/admin/tenant/switch'
 */
admin_tenant_switch.url = (options?: RouteQueryOptions) => {
    return admin_tenant_switch.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_tenant_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4988
 * @route '/admin/tenant/switch'
 */
admin_tenant_switch.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: admin_tenant_switch.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_tenant_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4988
 * @route '/admin/tenant/switch'
 */
    const admin_tenant_switchForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: admin_tenant_switch.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_tenant_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4988
 * @route '/admin/tenant/switch'
 */
        admin_tenant_switchForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: admin_tenant_switch.url(options),
            method: 'post',
        })
    
    admin_tenant_switch.form = admin_tenant_switchForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_pool_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4940
 * @route '/admin/pool/switch'
 */
export const admin_pool_switch = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: admin_pool_switch.url(options),
    method: 'post',
})

admin_pool_switch.definition = {
    methods: ["post"],
    url: '/admin/pool/switch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_pool_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4940
 * @route '/admin/pool/switch'
 */
admin_pool_switch.url = (options?: RouteQueryOptions) => {
    return admin_pool_switch.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_pool_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4940
 * @route '/admin/pool/switch'
 */
admin_pool_switch.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: admin_pool_switch.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_pool_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4940
 * @route '/admin/pool/switch'
 */
    const admin_pool_switchForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: admin_pool_switch.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::admin_pool_switch
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4940
 * @route '/admin/pool/switch'
 */
        admin_pool_switchForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: admin_pool_switch.url(options),
            method: 'post',
        })
    
    admin_pool_switch.form = admin_pool_switchForm