import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from '../../wayfinder'
import unitsD09b1b from './units'
import armadas172c7e from './armadas'
import flowsF41fe0 from './flows'
import master07b3d0 from './master'
/**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
export const bookingOnline = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: bookingOnline.url(options),
    method: 'get',
})

bookingOnline.definition = {
    methods: ["get","head"],
    url: '/settings/booking-online',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
bookingOnline.url = (options?: RouteQueryOptions) => {
    return bookingOnline.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
bookingOnline.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: bookingOnline.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
bookingOnline.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: bookingOnline.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
    const bookingOnlineForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: bookingOnline.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
        bookingOnlineForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: bookingOnline.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicBookingAdminController::bookingOnline
 * @see app/Http/Controllers/PublicBookingAdminController.php:15
 * @route '/settings/booking-online'
 */
        bookingOnlineForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: bookingOnline.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })

    bookingOnline.form = bookingOnlineForm
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
export const routes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routes.url(options),
    method: 'get',
})

routes.definition = {
    methods: ["get","head"],
    url: '/settings/rute-induk',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
routes.url = (options?: RouteQueryOptions) => {
    return routes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
routes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
routes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: routes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
    const routesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: routes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
        routesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
export const schedules = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})

schedules.definition = {
    methods: ["get","head"],
    url: '/settings/jadwal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
schedules.url = (options?: RouteQueryOptions) => {
    return schedules.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
schedules.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
schedules.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: schedules.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
    const schedulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: schedules.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
        schedulesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
export const drivers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})

drivers.definition = {
    methods: ["get","head"],
    url: '/settings/driver',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
drivers.url = (options?: RouteQueryOptions) => {
    return drivers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
drivers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
drivers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: drivers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
    const driversForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: drivers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
        driversForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: drivers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
export const services = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: services.url(options),
    method: 'get',
})

services.definition = {
    methods: ["get","head"],
    url: '/settings/tarif-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
services.url = (options?: RouteQueryOptions) => {
    return services.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
services.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: services.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
services.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: services.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
    const servicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: services.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
        servicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: services.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
export const segments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})

segments.definition = {
    methods: ["get","head"],
    url: '/settings/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
segments.url = (options?: RouteQueryOptions) => {
    return segments.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
segments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
segments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: segments.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
    const segmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: segments.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
        segmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segments.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
export const customers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})

customers.definition = {
    methods: ["get","head"],
    url: '/settings/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
customers.url = (options?: RouteQueryOptions) => {
    return customers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
customers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
customers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
    const customersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
        customersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
export const units = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})

units.definition = {
    methods: ["get","head"],
    url: '/settings/kategori-armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
units.url = (options?: RouteQueryOptions) => {
    return units.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
units.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
units.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: units.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
    const unitsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: units.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
        unitsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: units.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
export const armadas = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})

armadas.definition = {
    methods: ["get","head"],
    url: '/settings/armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
armadas.url = (options?: RouteQueryOptions) => {
    return armadas.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
armadas.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
armadas.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadas.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
    const armadasForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadas.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
        armadasForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadas.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
export const pools = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pools.url(options),
    method: 'get',
})

pools.definition = {
    methods: ["get","head"],
    url: '/settings/pool',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
pools.url = (options?: RouteQueryOptions) => {
    return pools.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
pools.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pools.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
pools.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pools.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
    const poolsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pools.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
        poolsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pools.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
export const users = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})

users.definition = {
    methods: ["get","head"],
    url: '/settings/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
users.url = (options?: RouteQueryOptions) => {
    return users.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
users.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: users.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
users.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: users.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
    const usersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: users.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
        usersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: users.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
export const roles = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})

roles.definition = {
    methods: ["get","head"],
    url: '/settings/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
roles.url = (options?: RouteQueryOptions) => {
    return roles.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
roles.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: roles.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
roles.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: roles.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
    const rolesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: roles.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
        rolesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: roles.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
export const logs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})

logs.definition = {
    methods: ["get","head"],
    url: '/settings/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
logs.url = (options?: RouteQueryOptions) => {
    return logs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
logs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
logs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logs.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
    const logsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logs.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
        logsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logs.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
export const reports = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reports.url(options),
    method: 'get',
})

reports.definition = {
    methods: ["get","head"],
    url: '/settings/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
reports.url = (options?: RouteQueryOptions) => {
    return reports.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
reports.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reports.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
reports.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reports.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
    const reportsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reports.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
        reportsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reports.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
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
 * @route '/settings/flows'
 */
export const flows = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flows.url(options),
    method: 'get',
})

flows.definition = {
    methods: ["get","head"],
    url: '/settings/flows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
flows.url = (options?: RouteQueryOptions) => {
    return flows.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
flows.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flows.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
flows.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: flows.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
    const flowsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: flows.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
 */
        flowsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: flows.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/settings/flows'
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
 * @route '/settings/master'
 */
export const master = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: master.url(options),
    method: 'get',
})

master.definition = {
    methods: ["get","head"],
    url: '/settings/master',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
master.url = (options?: RouteQueryOptions) => {
    return master.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
master.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: master.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
master.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: master.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
    const masterForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: master.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
 */
        masterForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: master.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsMasterController::__invoke
 * @see app/Http/Controllers/AdminOpsMasterController.php:21
 * @route '/settings/master'
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
const settings = {
    bookingOnline: Object.assign(bookingOnline, bookingOnline),
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
}

export default settings
