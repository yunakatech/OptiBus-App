import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
export const charterRoutes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charterRoutes.url(options),
    method: 'get',
})

charterRoutes.definition = {
    methods: ["get","head"],
    url: '/api/master/charter-routes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
charterRoutes.url = (options?: RouteQueryOptions) => {
    return charterRoutes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
charterRoutes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charterRoutes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
charterRoutes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: charterRoutes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
    const charterRoutesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: charterRoutes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
        charterRoutesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charterRoutes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::charterRoutes
 * @see app/Http/Controllers/Api/OperationsApiController.php:19
 * @route '/api/master/charter-routes'
 */
        charterRoutesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charterRoutes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    charterRoutes.form = charterRoutesForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
export const segments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})

segments.definition = {
    methods: ["get","head"],
    url: '/api/master/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
segments.url = (options?: RouteQueryOptions) => {
    return segments.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
segments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segments.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
segments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: segments.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
    const segmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: segments.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
 */
        segmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segments.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::segments
 * @see app/Http/Controllers/Api/OperationsApiController.php:34
 * @route '/api/master/segments'
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
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
export const segmentPrice = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segmentPrice.url(options),
    method: 'get',
})

segmentPrice.definition = {
    methods: ["get","head"],
    url: '/api/master/segment-price',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
segmentPrice.url = (options?: RouteQueryOptions) => {
    return segmentPrice.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
segmentPrice.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: segmentPrice.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
segmentPrice.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: segmentPrice.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
    const segmentPriceForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: segmentPrice.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
        segmentPriceForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segmentPrice.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::segmentPrice
 * @see app/Http/Controllers/Api/OperationsApiController.php:73
 * @route '/api/master/segment-price'
 */
        segmentPriceForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: segmentPrice.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    segmentPrice.form = segmentPriceForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
export const units = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})

units.definition = {
    methods: ["get","head"],
    url: '/api/master/units',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
units.url = (options?: RouteQueryOptions) => {
    return units.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
units.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: units.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
units.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: units.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
    const unitsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: units.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
 */
        unitsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: units.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::units
 * @see app/Http/Controllers/Api/OperationsApiController.php:91
 * @route '/api/master/units'
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
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
export const armadas = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})

armadas.definition = {
    methods: ["get","head"],
    url: '/api/master/armadas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
armadas.url = (options?: RouteQueryOptions) => {
    return armadas.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
armadas.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: armadas.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
armadas.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: armadas.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
    const armadasForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: armadas.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
 */
        armadasForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: armadas.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::armadas
 * @see app/Http/Controllers/Api/OperationsApiController.php:128
 * @route '/api/master/armadas'
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
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
export const drivers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})

drivers.definition = {
    methods: ["get","head"],
    url: '/api/master/drivers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
drivers.url = (options?: RouteQueryOptions) => {
    return drivers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
drivers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: drivers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
drivers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: drivers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
    const driversForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: drivers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
 */
        driversForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: drivers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::drivers
 * @see app/Http/Controllers/Api/OperationsApiController.php:187
 * @route '/api/master/drivers'
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
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
export const luggageServices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggageServices.url(options),
    method: 'get',
})

luggageServices.definition = {
    methods: ["get","head"],
    url: '/api/master/luggage-services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
luggageServices.url = (options?: RouteQueryOptions) => {
    return luggageServices.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
luggageServices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggageServices.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
luggageServices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: luggageServices.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
    const luggageServicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: luggageServices.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
        luggageServicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggageServices.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::luggageServices
 * @see app/Http/Controllers/Api/OperationsApiController.php:212
 * @route '/api/master/luggage-services'
 */
        luggageServicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggageServices.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    luggageServices.form = luggageServicesForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
export const searchCustomers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchCustomers.url(options),
    method: 'get',
})

searchCustomers.definition = {
    methods: ["get","head"],
    url: '/api/master/customers/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
searchCustomers.url = (options?: RouteQueryOptions) => {
    return searchCustomers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
searchCustomers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchCustomers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
searchCustomers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchCustomers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
    const searchCustomersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: searchCustomers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
        searchCustomersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchCustomers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\OperationsApiController::searchCustomers
 * @see app/Http/Controllers/Api/OperationsApiController.php:225
 * @route '/api/master/customers/search'
 */
        searchCustomersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchCustomers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    searchCustomers.form = searchCustomersForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitCharter
 * @see app/Http/Controllers/Api/OperationsApiController.php:324
 * @route '/api/ops/charters'
 */
export const submitCharter = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitCharter.url(options),
    method: 'post',
})

submitCharter.definition = {
    methods: ["post"],
    url: '/api/ops/charters',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitCharter
 * @see app/Http/Controllers/Api/OperationsApiController.php:324
 * @route '/api/ops/charters'
 */
submitCharter.url = (options?: RouteQueryOptions) => {
    return submitCharter.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitCharter
 * @see app/Http/Controllers/Api/OperationsApiController.php:324
 * @route '/api/ops/charters'
 */
submitCharter.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitCharter.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitCharter
 * @see app/Http/Controllers/Api/OperationsApiController.php:324
 * @route '/api/ops/charters'
 */
    const submitCharterForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submitCharter.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitCharter
 * @see app/Http/Controllers/Api/OperationsApiController.php:324
 * @route '/api/ops/charters'
 */
        submitCharterForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submitCharter.url(options),
            method: 'post',
        })
    
    submitCharter.form = submitCharterForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
const submitLuggage26f51b2e82e02ab5f683811521db4fc2 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitLuggage26f51b2e82e02ab5f683811521db4fc2.url(options),
    method: 'post',
})

submitLuggage26f51b2e82e02ab5f683811521db4fc2.definition = {
    methods: ["post"],
    url: '/api/ops/luggages',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
submitLuggage26f51b2e82e02ab5f683811521db4fc2.url = (options?: RouteQueryOptions) => {
    return submitLuggage26f51b2e82e02ab5f683811521db4fc2.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
submitLuggage26f51b2e82e02ab5f683811521db4fc2.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitLuggage26f51b2e82e02ab5f683811521db4fc2.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
    const submitLuggage26f51b2e82e02ab5f683811521db4fc2Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submitLuggage26f51b2e82e02ab5f683811521db4fc2.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
        submitLuggage26f51b2e82e02ab5f683811521db4fc2Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submitLuggage26f51b2e82e02ab5f683811521db4fc2.url(options),
            method: 'post',
        })
    
    submitLuggage26f51b2e82e02ab5f683811521db4fc2.form = submitLuggage26f51b2e82e02ab5f683811521db4fc2Form
    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
const submitLuggage164342cb13dcb791284a82858589fa0c = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitLuggage164342cb13dcb791284a82858589fa0c.url(options),
    method: 'post',
})

submitLuggage164342cb13dcb791284a82858589fa0c.definition = {
    methods: ["post"],
    url: '/api/ops/luggages/raw',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
submitLuggage164342cb13dcb791284a82858589fa0c.url = (options?: RouteQueryOptions) => {
    return submitLuggage164342cb13dcb791284a82858589fa0c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
submitLuggage164342cb13dcb791284a82858589fa0c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitLuggage164342cb13dcb791284a82858589fa0c.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
    const submitLuggage164342cb13dcb791284a82858589fa0cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submitLuggage164342cb13dcb791284a82858589fa0c.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitLuggage
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
        submitLuggage164342cb13dcb791284a82858589fa0cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submitLuggage164342cb13dcb791284a82858589fa0c.url(options),
            method: 'post',
        })
    
    submitLuggage164342cb13dcb791284a82858589fa0c.form = submitLuggage164342cb13dcb791284a82858589fa0cForm

/**
* Multiple routes resolve to \App\Http\Controllers\Api\OperationsApiController::submitLuggage, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `submitLuggage['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const submitLuggage = {
    '/api/ops/luggages': submitLuggage26f51b2e82e02ab5f683811521db4fc2,
    '/api/ops/luggages/raw': submitLuggage164342cb13dcb791284a82858589fa0c,
}

const OperationsApiController = { charterRoutes, segments, segmentPrice, units, armadas, drivers, luggageServices, searchCustomers, submitCharter, submitLuggage }

export default OperationsApiController