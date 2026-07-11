import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
export const welcome = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: welcome.url(options),
    method: 'get',
})

welcome.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
welcome.url = (options?: RouteQueryOptions) => {
    return welcome.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
welcome.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: welcome.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
welcome.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: welcome.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
    const welcomeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: welcome.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
        welcomeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: welcome.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicController::welcome
 * @see app/Http/Controllers/PublicController.php:17
 * @route '/'
 */
        welcomeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: welcome.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    welcome.form = welcomeForm
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
const PublicController = { welcome, pricing }

export default PublicController