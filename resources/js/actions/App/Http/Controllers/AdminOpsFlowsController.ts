import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters'
 */
AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters'
 */
    const AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters'
 */
        AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerd865f12c35ac209a4e3bed238017a01a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/form'
 */
AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/form'
 */
    const AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/form'
 */
        AdminOpsFlowsControllered85c6be014be94e27e1101d27521353Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllered85c6be014be94e27e1101d27521353.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/view/{id}'
 */
AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/view/{id}'
 */
AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/view/{id}'
 */
    const AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/charters/view/{id}'
 */
        AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dcForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerbcf2e188fc497345f4ad1e39aa1443dc.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages'
 */
AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages'
 */
    const AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages'
 */
        AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController5043c3252061633e62c90fc32ed31a79.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages/form'
 */
AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages/form'
 */
    const AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/luggages/form'
 */
        AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController9e4951b621f42e438847c3880fdd57a2.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
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
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
const AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url(options),
    method: 'get',
})

AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.definition = {
    methods: ["get","head"],
    url: '/settings/flows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
    const AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
        AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
        AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a.form = AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06aForm
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
const AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url(options),
    method: 'get',
})

AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.definition = {
    methods: ["get","head"],
    url: '/settings/flows/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
    const AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
        AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/charters'
 */
        AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1.form = AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
const AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url(options),
    method: 'get',
})

AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.definition = {
    methods: ["get","head"],
    url: '/settings/flows/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
    const AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
        AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/luggages'
 */
        AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75.form = AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
const AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url(options),
    method: 'get',
})

AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.definition = {
    methods: ["get","head"],
    url: '/settings/flows/assignments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
    const AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
        AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/assignments'
 */
        AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503.form = AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503Form
    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
const AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url(options),
    method: 'get',
})

AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.definition = {
    methods: ["get","head"],
    url: '/settings/flows/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url = (options?: RouteQueryOptions) => {
    return AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
    const AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
        AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows/export'
 */
        AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672.form = AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672Form

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
    '/settings/flows': AdminOpsFlowsControllerdb64617641f23a5d48abb43670c2a06a,
    '/settings/flows/charters': AdminOpsFlowsController4c8a1f2f8e2c0af1ed903f533e1ddcc1,
    '/settings/flows/luggages': AdminOpsFlowsController2c4956cf44351842c8cc74a68ef54c75,
    '/settings/flows/assignments': AdminOpsFlowsController18a918ca6cfdacc0ad693d1fe9436503,
    '/settings/flows/export': AdminOpsFlowsController883f95c424f0e8c29ea9c98a6ec08672,
}

export default AdminOpsFlowsController