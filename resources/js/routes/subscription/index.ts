import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/subscription',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SubscriptionPaymentController::index
 * @see app/Http/Controllers/SubscriptionPaymentController.php:23
 * @route '/subscription'
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
* @see \App\Http\Controllers\SubscriptionPaymentController::checkout
 * @see app/Http/Controllers/SubscriptionPaymentController.php:129
 * @route '/subscription/checkout'
 */
export const checkout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(options),
    method: 'post',
})

checkout.definition = {
    methods: ["post"],
    url: '/subscription/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SubscriptionPaymentController::checkout
 * @see app/Http/Controllers/SubscriptionPaymentController.php:129
 * @route '/subscription/checkout'
 */
checkout.url = (options?: RouteQueryOptions) => {
    return checkout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SubscriptionPaymentController::checkout
 * @see app/Http/Controllers/SubscriptionPaymentController.php:129
 * @route '/subscription/checkout'
 */
checkout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SubscriptionPaymentController::checkout
 * @see app/Http/Controllers/SubscriptionPaymentController.php:129
 * @route '/subscription/checkout'
 */
    const checkoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SubscriptionPaymentController::checkout
 * @see app/Http/Controllers/SubscriptionPaymentController.php:129
 * @route '/subscription/checkout'
 */
        checkoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkout.url(options),
            method: 'post',
        })
    
    checkout.form = checkoutForm
const subscription = {
    index: Object.assign(index, index),
checkout: Object.assign(checkout, checkout),
}

export default subscription