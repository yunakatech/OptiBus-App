import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
const AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url(options),
    method: 'get',
})

AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.definition = {
    methods: ["get","head"],
    url: '/admin-ops/master',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
    const AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
        AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
        AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177.form = AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
const AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url(options),
    method: 'get',
})

AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.definition = {
    methods: ["get","head"],
    url: '/admin-ops/customer-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
    const AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
        AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-bagasi'
 */
        AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0.form = AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
const AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url(options),
    method: 'get',
})

AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.definition = {
    methods: ["get","head"],
    url: '/admin-ops/customer-charter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
    const AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
        AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/customer-charter'
 */
        AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73.form = AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
const AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url(options),
    method: 'get',
})

AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.definition = {
    methods: ["get","head"],
    url: '/admin-ops/rute-carter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
    const AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
        AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/rute-carter'
 */
        AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c.form = AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3cForm

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsMasterController::AdminOpsMasterController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsMasterController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsMasterController = {
    '/admin-ops/master': AdminOpsMasterController99dcf4e5114f0157c7949a1666f34177,
    '/admin-ops/customer-bagasi': AdminOpsMasterControllerf6e2fa89769ccc29a63d6e9212720ae0,
    '/admin-ops/customer-charter': AdminOpsMasterControllerbbd34c32bb3de4130a7658155ccf7f73,
    '/admin-ops/rute-carter': AdminOpsMasterController157c0177bcac9a56ac828d7f2c4f7c3c,
}

export default AdminOpsMasterController