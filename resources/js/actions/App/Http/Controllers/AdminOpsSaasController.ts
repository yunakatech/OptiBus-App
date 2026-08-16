import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
const AdminOpsSaasController3514beee837631acd00c06fa6b282362 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url(options),
    method: 'get',
})

AdminOpsSaasController3514beee837631acd00c06fa6b282362.definition = {
    methods: ["get","head"],
    url: '/platform/saas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
AdminOpsSaasController3514beee837631acd00c06fa6b282362.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasController3514beee837631acd00c06fa6b282362.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
AdminOpsSaasController3514beee837631acd00c06fa6b282362.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
AdminOpsSaasController3514beee837631acd00c06fa6b282362.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
    const AdminOpsSaasController3514beee837631acd00c06fa6b282362Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
        AdminOpsSaasController3514beee837631acd00c06fa6b282362Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas'
 */
        AdminOpsSaasController3514beee837631acd00c06fa6b282362Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController3514beee837631acd00c06fa6b282362.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasController3514beee837631acd00c06fa6b282362.form = AdminOpsSaasController3514beee837631acd00c06fa6b282362Form
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
const AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url(options),
    method: 'get',
})

AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.definition = {
    methods: ["get","head"],
    url: '/platform/saas/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
    const AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
        AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/tenants'
 */
        AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d.form = AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035dForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
const AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url(options),
    method: 'get',
})

AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.definition = {
    methods: ["get","head"],
    url: '/platform/saas/subscriptions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
    const AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
        AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/subscriptions'
 */
        AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703.form = AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703Form
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
const AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url(options),
    method: 'get',
})

AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.definition = {
    methods: ["get","head"],
    url: '/platform/saas/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
    const AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
        AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/plans'
 */
        AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a.form = AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0aForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
const AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url(options),
    method: 'get',
})

AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.definition = {
    methods: ["get","head"],
    url: '/platform/saas/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
    const AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
        AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/invoices'
 */
        AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c.form = AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85cForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
const AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url(options),
    method: 'get',
})

AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.definition = {
    methods: ["get","head"],
    url: '/platform/saas/payment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
    const AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3baForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
        AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3baForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:20
 * @route '/platform/saas/payment'
 */
        AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3baForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba.form = AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3baForm

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsSaasController::AdminOpsSaasController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsSaasController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsSaasController = {
    '/platform/saas': AdminOpsSaasController3514beee837631acd00c06fa6b282362,
    '/platform/saas/tenants': AdminOpsSaasController00a9a5e958e4cf092ea97432a9a8035d,
    '/platform/saas/subscriptions': AdminOpsSaasControllerc4aeb035a4d3f6d2ad61633c9a5d7703,
    '/platform/saas/plans': AdminOpsSaasControllerfef4d6fd244554d16d4646f6a6c4bc0a,
    '/platform/saas/invoices': AdminOpsSaasControllered0a5bab8c33b7aea7af0b7dfac3d85c,
    '/platform/saas/payment': AdminOpsSaasControllerfb344ac2470dc487fbe4740749c3f3ba,
}

export default AdminOpsSaasController