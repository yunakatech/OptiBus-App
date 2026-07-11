import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/onboarding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\OnboardingController::show
 * @see app/Http/Controllers/Auth/OnboardingController.php:23
 * @route '/onboarding'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Auth\OnboardingController::store
 * @see app/Http/Controllers/Auth/OnboardingController.php:35
 * @route '/onboarding'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/onboarding',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OnboardingController::store
 * @see app/Http/Controllers/Auth/OnboardingController.php:35
 * @route '/onboarding'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OnboardingController::store
 * @see app/Http/Controllers/Auth/OnboardingController.php:35
 * @route '/onboarding'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\OnboardingController::store
 * @see app/Http/Controllers/Auth/OnboardingController.php:35
 * @route '/onboarding'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\OnboardingController::store
 * @see app/Http/Controllers/Auth/OnboardingController.php:35
 * @route '/onboarding'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const OnboardingController = { show, store }

export default OnboardingController