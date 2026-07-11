import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
const AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
    method: 'get',
})

AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.definition = {
    methods: ["get","head"],
    url: '/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
    const AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
        AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
        AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.form = AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
const AdminOpsFlowsControllered85c6be014be94e27e1101d27521353 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
    method: 'get',
})

AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.definition = {
    methods: ["get","head"],
    url: '/charters/form',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
    const AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
        AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
        AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.form = AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
const AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
    method: 'get',
})

AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.definition = {
    methods: ["get","head"],
    url: '/charters/view/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
    const AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
        AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
        AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.form = AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
const AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
    method: 'get',
})

AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.definition = {
    methods: ["get","head"],
    url: '/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
    const AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
        AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages'
 */
        AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.form = AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
const AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
    method: 'get',
})

AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.definition = {
    methods: ["get","head"],
    url: '/luggages/form',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
    const AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
        AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/luggages/form'
 */
        AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.form = AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
const AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url(options),
    method: 'get',
})

AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
    const AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
        AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows'
 */
        AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44.form = AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
const AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url(options),
    method: 'get',
})

AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
    const AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
        AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/charters'
 */
        AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5.form = AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
const AdminOpsFlowsController1da64293d59593b05138346920a88c58 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url(options),
    method: 'get',
})

AdminOpsFlowsController1da64293d59593b05138346920a88c58.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
AdminOpsFlowsController1da64293d59593b05138346920a88c58.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController1da64293d59593b05138346920a88c58.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
AdminOpsFlowsController1da64293d59593b05138346920a88c58.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
AdminOpsFlowsController1da64293d59593b05138346920a88c58.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
    const AdminOpsFlowsController1da64293d59593b05138346920a88c58Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
        AdminOpsFlowsController1da64293d59593b05138346920a88c58Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/luggages'
 */
        AdminOpsFlowsController1da64293d59593b05138346920a88c58Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController1da64293d59593b05138346920a88c58.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController1da64293d59593b05138346920a88c58.form = AdminOpsFlowsController1da64293d59593b05138346920a88c58Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
const AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url(options),
    method: 'get',
})

AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/assignments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
    const AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
        AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/assignments'
 */
        AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22.form = AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
const AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url(options),
    method: 'get',
})

AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
    const AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
        AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/admin-ops/flows/export'
 */
        AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b.form = AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93bForm

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsFlowsController::AdminOpsFlowsController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsFlowsController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsFlowsController = {
    '/charters': AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a,
    '/charters/form': AdminOpsFlowsControllered85c6be014be94e27e1101d27521353,
    '/charters/view/{id}': AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc,
    '/luggages': AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79,
    '/luggages/form': AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2,
    '/admin-ops/flows': AdminOpsFlowsController855ed1ac914d1230d231dbb9cd953e44,
    '/admin-ops/flows/charters': AdminOpsFlowsControllerc97994798d51c68e567c458b89e12de5,
    '/admin-ops/flows/luggages': AdminOpsFlowsController1da64293d59593b05138346920a88c58,
    '/admin-ops/flows/assignments': AdminOpsFlowsController3e0732d06a87c58d6246acfb77c9cb22,
    '/admin-ops/flows/export': AdminOpsFlowsController8a257d0eb7ab56021b864dfd64b0b93b,
}

export default AdminOpsFlowsController