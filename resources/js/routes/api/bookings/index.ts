import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import departureRiturs4d7ec9 from './departure-riturs'
/**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
export const routesByDate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routesByDate.url(options),
    method: 'get',
})

routesByDate.definition = {
    methods: ["get","head"],
    url: '/api/bookings/routes-by-date',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
routesByDate.url = (options?: RouteQueryOptions) => {
    return routesByDate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
routesByDate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: routesByDate.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
routesByDate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: routesByDate.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
    const routesByDateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: routesByDate.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
        routesByDateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routesByDate.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BookingApiController::routesByDate
 * @see app/Http/Controllers/Api/BookingApiController.php:38
 * @route '/api/bookings/routes-by-date'
 */
        routesByDateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: routesByDate.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    routesByDate.form = routesByDateForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
export const schedules = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})

schedules.definition = {
    methods: ["get","head"],
    url: '/api/bookings/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
schedules.url = (options?: RouteQueryOptions) => {
    return schedules.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
schedules.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
schedules.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: schedules.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
    const schedulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: schedules.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
 */
        schedulesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BookingApiController::schedules
 * @see app/Http/Controllers/Api/BookingApiController.php:71
 * @route '/api/bookings/schedules'
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
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
export const seatsDetail = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seatsDetail.url(options),
    method: 'get',
})

seatsDetail.definition = {
    methods: ["get","head"],
    url: '/api/bookings/seats-detail',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
seatsDetail.url = (options?: RouteQueryOptions) => {
    return seatsDetail.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
seatsDetail.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seatsDetail.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
seatsDetail.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: seatsDetail.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
    const seatsDetailForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: seatsDetail.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
        seatsDetailForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seatsDetail.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BookingApiController::seatsDetail
 * @see app/Http/Controllers/Api/BookingApiController.php:385
 * @route '/api/bookings/seats-detail'
 */
        seatsDetailForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: seatsDetail.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    seatsDetail.form = seatsDetailForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
export const editSeatOptions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editSeatOptions.url(options),
    method: 'get',
})

editSeatOptions.definition = {
    methods: ["get","head"],
    url: '/api/bookings/edit-seat-options',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
editSeatOptions.url = (options?: RouteQueryOptions) => {
    return editSeatOptions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
editSeatOptions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editSeatOptions.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
editSeatOptions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editSeatOptions.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
    const editSeatOptionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: editSeatOptions.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
        editSeatOptionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editSeatOptions.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BookingApiController::editSeatOptions
 * @see app/Http/Controllers/Api/BookingApiController.php:483
 * @route '/api/bookings/edit-seat-options'
 */
        editSeatOptionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editSeatOptions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    editSeatOptions.form = editSeatOptionsForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
export const departureRiturs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: departureRiturs.url(options),
    method: 'get',
})

departureRiturs.definition = {
    methods: ["get","head"],
    url: '/api/bookings/departure-riturs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
departureRiturs.url = (options?: RouteQueryOptions) => {
    return departureRiturs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
departureRiturs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: departureRiturs.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
departureRiturs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: departureRiturs.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
    const departureRitursForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: departureRiturs.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
        departureRitursForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: departureRiturs.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\BookingApiController::departureRiturs
 * @see app/Http/Controllers/Api/BookingApiController.php:1180
 * @route '/api/bookings/departure-riturs'
 */
        departureRitursForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: departureRiturs.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    departureRiturs.form = departureRitursForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::emptyDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:757
 * @route '/api/bookings/empty-departure'
 */
export const emptyDeparture = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: emptyDeparture.url(options),
    method: 'post',
})

emptyDeparture.definition = {
    methods: ["post"],
    url: '/api/bookings/empty-departure',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::emptyDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:757
 * @route '/api/bookings/empty-departure'
 */
emptyDeparture.url = (options?: RouteQueryOptions) => {
    return emptyDeparture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::emptyDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:757
 * @route '/api/bookings/empty-departure'
 */
emptyDeparture.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: emptyDeparture.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::emptyDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:757
 * @route '/api/bookings/empty-departure'
 */
    const emptyDepartureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: emptyDeparture.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::emptyDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:757
 * @route '/api/bookings/empty-departure'
 */
        emptyDepartureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: emptyDeparture.url(options),
            method: 'post',
        })
    
    emptyDeparture.form = emptyDepartureForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::cancelDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:846
 * @route '/api/bookings/cancel-departure'
 */
export const cancelDeparture = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelDeparture.url(options),
    method: 'post',
})

cancelDeparture.definition = {
    methods: ["post"],
    url: '/api/bookings/cancel-departure',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::cancelDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:846
 * @route '/api/bookings/cancel-departure'
 */
cancelDeparture.url = (options?: RouteQueryOptions) => {
    return cancelDeparture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::cancelDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:846
 * @route '/api/bookings/cancel-departure'
 */
cancelDeparture.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelDeparture.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::cancelDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:846
 * @route '/api/bookings/cancel-departure'
 */
    const cancelDepartureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancelDeparture.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::cancelDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:846
 * @route '/api/bookings/cancel-departure'
 */
        cancelDepartureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancelDeparture.url(options),
            method: 'post',
        })
    
    cancelDeparture.form = cancelDepartureForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::departDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:934
 * @route '/api/bookings/depart-departure'
 */
export const departDeparture = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: departDeparture.url(options),
    method: 'post',
})

departDeparture.definition = {
    methods: ["post"],
    url: '/api/bookings/depart-departure',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::departDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:934
 * @route '/api/bookings/depart-departure'
 */
departDeparture.url = (options?: RouteQueryOptions) => {
    return departDeparture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::departDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:934
 * @route '/api/bookings/depart-departure'
 */
departDeparture.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: departDeparture.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::departDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:934
 * @route '/api/bookings/depart-departure'
 */
    const departDepartureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: departDeparture.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::departDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:934
 * @route '/api/bookings/depart-departure'
 */
        departDepartureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: departDeparture.url(options),
            method: 'post',
        })
    
    departDeparture.form = departDepartureForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::arriveDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:1020
 * @route '/api/bookings/arrive-departure'
 */
export const arriveDeparture = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: arriveDeparture.url(options),
    method: 'post',
})

arriveDeparture.definition = {
    methods: ["post"],
    url: '/api/bookings/arrive-departure',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::arriveDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:1020
 * @route '/api/bookings/arrive-departure'
 */
arriveDeparture.url = (options?: RouteQueryOptions) => {
    return arriveDeparture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::arriveDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:1020
 * @route '/api/bookings/arrive-departure'
 */
arriveDeparture.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: arriveDeparture.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::arriveDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:1020
 * @route '/api/bookings/arrive-departure'
 */
    const arriveDepartureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: arriveDeparture.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::arriveDeparture
 * @see app/Http/Controllers/Api/BookingApiController.php:1020
 * @route '/api/bookings/arrive-departure'
 */
        arriveDepartureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: arriveDeparture.url(options),
            method: 'post',
        })
    
    arriveDeparture.form = arriveDepartureForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::closeManifest
 * @see app/Http/Controllers/Api/BookingApiController.php:1126
 * @route '/api/bookings/close-manifest'
 */
export const closeManifest = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeManifest.url(options),
    method: 'post',
})

closeManifest.definition = {
    methods: ["post"],
    url: '/api/bookings/close-manifest',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::closeManifest
 * @see app/Http/Controllers/Api/BookingApiController.php:1126
 * @route '/api/bookings/close-manifest'
 */
closeManifest.url = (options?: RouteQueryOptions) => {
    return closeManifest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::closeManifest
 * @see app/Http/Controllers/Api/BookingApiController.php:1126
 * @route '/api/bookings/close-manifest'
 */
closeManifest.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeManifest.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::closeManifest
 * @see app/Http/Controllers/Api/BookingApiController.php:1126
 * @route '/api/bookings/close-manifest'
 */
    const closeManifestForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: closeManifest.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::closeManifest
 * @see app/Http/Controllers/Api/BookingApiController.php:1126
 * @route '/api/bookings/close-manifest'
 */
        closeManifestForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: closeManifest.url(options),
            method: 'post',
        })
    
    closeManifest.form = closeManifestForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::bulkPayment
 * @see app/Http/Controllers/Api/BookingApiController.php:1894
 * @route '/api/bookings/bulk-payment'
 */
export const bulkPayment = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkPayment.url(options),
    method: 'post',
})

bulkPayment.definition = {
    methods: ["post"],
    url: '/api/bookings/bulk-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::bulkPayment
 * @see app/Http/Controllers/Api/BookingApiController.php:1894
 * @route '/api/bookings/bulk-payment'
 */
bulkPayment.url = (options?: RouteQueryOptions) => {
    return bulkPayment.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::bulkPayment
 * @see app/Http/Controllers/Api/BookingApiController.php:1894
 * @route '/api/bookings/bulk-payment'
 */
bulkPayment.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkPayment.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::bulkPayment
 * @see app/Http/Controllers/Api/BookingApiController.php:1894
 * @route '/api/bookings/bulk-payment'
 */
    const bulkPaymentForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkPayment.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::bulkPayment
 * @see app/Http/Controllers/Api/BookingApiController.php:1894
 * @route '/api/bookings/bulk-payment'
 */
        bulkPaymentForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkPayment.url(options),
            method: 'post',
        })
    
    bulkPayment.form = bulkPaymentForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::submit
 * @see app/Http/Controllers/Api/BookingApiController.php:1481
 * @route '/api/bookings/submit'
 */
export const submit = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/api/bookings/submit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::submit
 * @see app/Http/Controllers/Api/BookingApiController.php:1481
 * @route '/api/bookings/submit'
 */
submit.url = (options?: RouteQueryOptions) => {
    return submit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::submit
 * @see app/Http/Controllers/Api/BookingApiController.php:1481
 * @route '/api/bookings/submit'
 */
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::submit
 * @see app/Http/Controllers/Api/BookingApiController.php:1481
 * @route '/api/bookings/submit'
 */
    const submitForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::submit
 * @see app/Http/Controllers/Api/BookingApiController.php:1481
 * @route '/api/bookings/submit'
 */
        submitForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(options),
            method: 'post',
        })
    
    submit.form = submitForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::update
 * @see app/Http/Controllers/Api/BookingApiController.php:1673
 * @route '/api/bookings/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/api/bookings/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::update
 * @see app/Http/Controllers/Api/BookingApiController.php:1673
 * @route '/api/bookings/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::update
 * @see app/Http/Controllers/Api/BookingApiController.php:1673
 * @route '/api/bookings/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::update
 * @see app/Http/Controllers/Api/BookingApiController.php:1673
 * @route '/api/bookings/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::update
 * @see app/Http/Controllers/Api/BookingApiController.php:1673
 * @route '/api/bookings/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Api\BookingApiController::cancel
 * @see app/Http/Controllers/Api/BookingApiController.php:1997
 * @route '/api/bookings/cancel'
 */
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/api/bookings/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingApiController::cancel
 * @see app/Http/Controllers/Api/BookingApiController.php:1997
 * @route '/api/bookings/cancel'
 */
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingApiController::cancel
 * @see app/Http/Controllers/Api/BookingApiController.php:1997
 * @route '/api/bookings/cancel'
 */
cancel.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingApiController::cancel
 * @see app/Http/Controllers/Api/BookingApiController.php:1997
 * @route '/api/bookings/cancel'
 */
    const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingApiController::cancel
 * @see app/Http/Controllers/Api/BookingApiController.php:1997
 * @route '/api/bookings/cancel'
 */
        cancelForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(options),
            method: 'post',
        })
    
    cancel.form = cancelForm
const bookings = {
    routesByDate: Object.assign(routesByDate, routesByDate),
schedules: Object.assign(schedules, schedules),
seatsDetail: Object.assign(seatsDetail, seatsDetail),
editSeatOptions: Object.assign(editSeatOptions, editSeatOptions),
departureRiturs: Object.assign(departureRiturs, departureRiturs4d7ec9),
emptyDeparture: Object.assign(emptyDeparture, emptyDeparture),
cancelDeparture: Object.assign(cancelDeparture, cancelDeparture),
departDeparture: Object.assign(departDeparture, departDeparture),
arriveDeparture: Object.assign(arriveDeparture, arriveDeparture),
closeManifest: Object.assign(closeManifest, closeManifest),
bulkPayment: Object.assign(bulkPayment, bulkPayment),
submit: Object.assign(submit, submit),
update: Object.assign(update, update),
cancel: Object.assign(cancel, cancel),
}

export default bookings