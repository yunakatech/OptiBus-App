import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
const AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url(options),
    method: 'get',
})

AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.definition = {
    methods: ["get","head"],
    url: '/settings/master',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
    const AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
        AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
        AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61.form = AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
const AdminOpsMasterController0ec646fc87e999058d0b45e47390c450 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url(options),
    method: 'get',
})

AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.definition = {
    methods: ["get","head"],
    url: '/settings/customer-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
    const AdminOpsMasterController0ec646fc87e999058d0b45e47390c450Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
        AdminOpsMasterController0ec646fc87e999058d0b45e47390c450Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-bagasi'
 */
        AdminOpsMasterController0ec646fc87e999058d0b45e47390c450Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsMasterController0ec646fc87e999058d0b45e47390c450.form = AdminOpsMasterController0ec646fc87e999058d0b45e47390c450Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
const AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url(options),
    method: 'get',
})

AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.definition = {
    methods: ["get","head"],
    url: '/settings/customer-charter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
    const AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
        AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/customer-charter'
 */
        AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2.form = AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2Form
    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
const AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url(options),
    method: 'get',
})

AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.definition = {
    methods: ["get","head"],
    url: '/settings/rute-carter',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url = (options?: RouteQueryOptions) => {
    return AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
    const AdminOpsMasterController349455c0a556ef8a8e721305d8d4234fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
        AdminOpsMasterController349455c0a556ef8a8e721305d8d4234fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/rute-carter'
 */
        AdminOpsMasterController349455c0a556ef8a8e721305d8d4234fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f.form = AdminOpsMasterController349455c0a556ef8a8e721305d8d4234fForm

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsMasterController::AdminOpsMasterController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsMasterController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsMasterController = {
    '/settings/master': AdminOpsMasterController7ae2fc32773d236aa93dbc56d3140f61,
    '/settings/customer-bagasi': AdminOpsMasterController0ec646fc87e999058d0b45e47390c450,
    '/settings/customer-charter': AdminOpsMasterController0ac3d28aa7b69d33ede48ea3d6b261a2,
    '/settings/rute-carter': AdminOpsMasterController349455c0a556ef8a8e721305d8d4234f,
}

export default AdminOpsMasterController