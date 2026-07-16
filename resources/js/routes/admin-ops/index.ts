import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import unitsD09b1b from './units'
import armadas172c7e from './armadas'
import flowsF41fe0 from './flows'
import master07b3d0 from './master'
import saasB7f852 from './saas'
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin-ops',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
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
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
export const routes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routes.url(options),
    method: 'get',
})

routes.definition = {
    methods: ["get","head"],
    url: '/admin-ops/rute-induk',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
routes.url = (options?: RouteQueryOptions) => {
    return routes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
routes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
routes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: routes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
    const routesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: routes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
        routesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
        routesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    routes.form = routesForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
export const schedules = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})

schedules.definition = {
    methods: ["get","head"],
    url: '/admin-ops/jadwal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
schedules.url = (options?: RouteQueryOptions) => {
    return schedules.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
schedules.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
schedules.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: schedules.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
    const schedulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: schedules.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
        schedulesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
        schedulesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    schedules.form = schedulesForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
export const drivers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})

drivers.definition = {
    methods: ["get","head"],
    url: '/admin-ops/driver',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
drivers.url = (options?: RouteQueryOptions) => {
    return drivers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
drivers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
drivers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: drivers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
    const driversForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: drivers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
        driversForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: drivers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
        driversForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: drivers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    drivers.form = driversForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
export const services = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: services.url(options),
    method: 'get',
})

services.definition = {
    methods: ["get","head"],
    url: '/admin-ops/tarif-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
services.url = (options?: RouteQueryOptions) => {
    return services.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
services.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: services.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
services.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: services.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
    const servicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: services.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
        servicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: services.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
        servicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: services.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    services.form = servicesForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
export const segments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})

segments.definition = {
    methods: ["get","head"],
    url: '/admin-ops/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
segments.url = (options?: RouteQueryOptions) => {
    return segments.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
segments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
segments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: segments.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
    const segmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: segments.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
        segmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segments.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
        segmentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segments.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    segments.form = segmentsForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
export const customers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})

customers.definition = {
    methods: ["get","head"],
    url: '/admin-ops/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
customers.url = (options?: RouteQueryOptions) => {
    return customers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
customers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
customers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
    const customersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
        customersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
        customersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customers.form = customersForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
export const units = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})

units.definition = {
    methods: ["get","head"],
    url: '/admin-ops/kategori-armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
units.url = (options?: RouteQueryOptions) => {
    return units.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
units.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
units.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: units.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
    const unitsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: units.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
        unitsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: units.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
        unitsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: units.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    units.form = unitsForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
export const armadas = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})

armadas.definition = {
    methods: ["get","head"],
    url: '/admin-ops/armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
armadas.url = (options?: RouteQueryOptions) => {
    return armadas.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
armadas.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
armadas.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadas.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
    const armadasForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadas.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
        armadasForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadas.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
        armadasForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadas.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    armadas.form = armadasForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
export const pools = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pools.url(options),
    method: 'get',
})

pools.definition = {
    methods: ["get","head"],
    url: '/admin-ops/pool',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
pools.url = (options?: RouteQueryOptions) => {
    return pools.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
pools.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pools.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
pools.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pools.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
    const poolsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pools.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
        poolsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pools.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
        poolsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pools.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pools.form = poolsForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
export const users = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

users.definition = {
    methods: ["get","head"],
    url: '/admin-ops/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
users.url = (options?: RouteQueryOptions) => {
    return users.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
users.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
users.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: users.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
    const usersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: users.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
        usersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
        usersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    users.form = usersForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
export const roles = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})

roles.definition = {
    methods: ["get","head"],
    url: '/admin-ops/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
roles.url = (options?: RouteQueryOptions) => {
    return roles.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
roles.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
roles.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: roles.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
    const rolesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: roles.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
        rolesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: roles.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
        rolesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: roles.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    roles.form = rolesForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
export const logs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})

logs.definition = {
    methods: ["get","head"],
    url: '/admin-ops/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
logs.url = (options?: RouteQueryOptions) => {
    return logs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
logs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
logs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logs.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
    const logsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logs.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
        logsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logs.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
        logsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logs.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    logs.form = logsForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
export const reports = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reports.url(options),
    method: 'get',
})

reports.definition = {
    methods: ["get","head"],
    url: '/admin-ops/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
reports.url = (options?: RouteQueryOptions) => {
    return reports.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
reports.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reports.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
reports.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reports.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
    const reportsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reports.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
        reportsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reports.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
        reportsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reports.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    reports.form = reportsForm
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
export const flows = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flows.url(options),
    method: 'get',
})

flows.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
flows.url = (options?: RouteQueryOptions) => {
    return flows.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
flows.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flows.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
flows.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: flows.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
    const flowsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: flows.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
        flowsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: flows.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows'
 */
        flowsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: flows.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    flows.form = flowsForm
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
export const master = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: master.url(options),
    method: 'get',
})

master.definition = {
    methods: ["get","head"],
    url: '/admin-ops/master',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
master.url = (options?: RouteQueryOptions) => {
    return master.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
master.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: master.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
master.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: master.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
    const masterForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: master.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
        masterForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: master.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/admin-ops/master'
 */
        masterForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: master.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    master.form = masterForm
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
export const saas = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: saas.url(options),
    method: 'get',
})

saas.definition = {
    methods: ["get","head"],
    url: '/admin-ops/saas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
saas.url = (options?: RouteQueryOptions) => {
    return saas.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
saas.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: saas.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
saas.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: saas.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
    const saasForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: saas.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
        saasForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: saas.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsSaasController::__invoke
 * @see app/Http/Controllers/AdminOpsSaasController.php:19
 * @route '/admin-ops/saas'
 */
        saasForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: saas.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    saas.form = saasForm
const adminOps = {
    index: Object.assign(index, index),
routes: Object.assign(routes, routes),
schedules: Object.assign(schedules, schedules),
drivers: Object.assign(drivers, drivers),
services: Object.assign(services, services),
segments: Object.assign(segments, segments),
customers: Object.assign(customers, customers),
units: Object.assign(units, unitsD09b1b),
armadas: Object.assign(armadas, armadas172c7e),
pools: Object.assign(pools, pools),
users: Object.assign(users, users),
roles: Object.assign(roles, roles),
logs: Object.assign(logs, logs),
reports: Object.assign(reports, reports),
flows: Object.assign(flows, flowsF41fe0),
master: Object.assign(master, master07b3d0),
saas: Object.assign(saas, saasB7f852),
}

export default adminOps