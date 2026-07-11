import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
export const tenants = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tenants.url(options),
    method: 'get',
})

tenants.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
tenants.url = (options?: RouteQueryOptions) => {
    return tenants.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
tenants.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tenants.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
tenants.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tenants.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
    const tenantsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tenants.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
        tenantsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tenants.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
        tenantsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tenants.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    tenants.form = tenantsForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
export const subscriptions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: subscriptions.url(options),
    method: 'get',
})

subscriptions.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/subscriptions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
subscriptions.url = (options?: RouteQueryOptions) => {
    return subscriptions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
subscriptions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: subscriptions.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
subscriptions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: subscriptions.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
    const subscriptionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: subscriptions.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
        subscriptionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: subscriptions.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
        subscriptionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: subscriptions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    subscriptions.form = subscriptionsForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
export const plans = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

plans.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
plans.url = (options?: RouteQueryOptions) => {
    return plans.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
plans.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
plans.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plans.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
    const plansForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: plans.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
        plansForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
        plansForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    plans.form = plansForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
export const invoices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})

invoices.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
invoices.url = (options?: RouteQueryOptions) => {
    return invoices.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
invoices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
invoices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoices.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
    const invoicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoices.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
        invoicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoices.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
        invoicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoices.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    invoices.form = invoicesForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
export const payment = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payment.url(options),
    method: 'get',
})

payment.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/payment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
payment.url = (options?: RouteQueryOptions) => {
    return payment.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
payment.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payment.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
payment.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: payment.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
    const paymentForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: payment.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
        paymentForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payment.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
        paymentForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payment.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    payment.form = paymentForm
const saas = {
    tenants: Object.assign(tenants, tenants),
subscriptions: Object.assign(subscriptions, subscriptions),
plans: Object.assign(plans, plans),
invoices: Object.assign(invoices, invoices),
payment: Object.assign(payment, payment),
}

export default saas