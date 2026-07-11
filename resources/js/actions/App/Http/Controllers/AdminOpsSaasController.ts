import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
const AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url(options),
    method: 'get',
})

AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
    const AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
        AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
        AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b.form = AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86bForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
const AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url(options),
    method: 'get',
})

AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
    const AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556ecaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
        AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556ecaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/tenants'
 */
        AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556ecaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca.form = AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556ecaForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
const AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url(options),
    method: 'get',
})

AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/subscriptions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
    const AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
        AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/subscriptions'
 */
        AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9.form = AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9Form
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
const AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url(options),
    method: 'get',
})

AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
    const AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
        AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/plans'
 */
        AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d.form = AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2dForm
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
const AdminOpsSaasController594c5c73392af84566a43ab28e656222 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url(options),
    method: 'get',
})

AdminOpsSaasController594c5c73392af84566a43ab28e656222.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
AdminOpsSaasController594c5c73392af84566a43ab28e656222.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasController594c5c73392af84566a43ab28e656222.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
AdminOpsSaasController594c5c73392af84566a43ab28e656222.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
AdminOpsSaasController594c5c73392af84566a43ab28e656222.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
    const AdminOpsSaasController594c5c73392af84566a43ab28e656222Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
        AdminOpsSaasController594c5c73392af84566a43ab28e656222Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/invoices'
 */
        AdminOpsSaasController594c5c73392af84566a43ab28e656222Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController594c5c73392af84566a43ab28e656222.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasController594c5c73392af84566a43ab28e656222.form = AdminOpsSaasController594c5c73392af84566a43ab28e656222Form
    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
const AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url(options),
    method: 'get',
})

AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas/payment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url = (options?: RouteQueryOptions) => {
    return AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
    const AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
        AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas/payment'
 */
        AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f.form = AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55fForm

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsSaasController::AdminOpsSaasController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsSaasController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsSaasController = {
    '/admin-ops/saas': AdminOpsSaasControllere9be78b868bfc1c76cb38c3fbe68c86b,
    '/admin-ops/saas/tenants': AdminOpsSaasControlleraae2366b5e22041816d5a39d8b556eca,
    '/admin-ops/saas/subscriptions': AdminOpsSaasControllereb0a1fe7621136537fb0ef1eade501a9,
    '/admin-ops/saas/plans': AdminOpsSaasController0c3db54cbdb1307b83370f1e172d3a2d,
    '/admin-ops/saas/invoices': AdminOpsSaasController594c5c73392af84566a43ab28e656222,
    '/admin-ops/saas/payment': AdminOpsSaasController3e3d19ae608d61784ecaaafae3dfc55f,
}

export default AdminOpsSaasController