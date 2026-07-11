import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
const BookingControllerc788df35b14daebdc29d781f7cbb0fba = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url(options),
    method: 'get',
})

BookingControllerc788df35b14daebdc29d781f7cbb0fba.definition = {
    methods: ["get","head"],
    url: '/bookings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
BookingControllerc788df35b14daebdc29d781f7cbb0fba.url = (options?: RouteQueryOptions) => {
    return BookingControllerc788df35b14daebdc29d781f7cbb0fba.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
BookingControllerc788df35b14daebdc29d781f7cbb0fba.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
BookingControllerc788df35b14daebdc29d781f7cbb0fba.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
    const BookingControllerc788df35b14daebdc29d781f7cbb0fbaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
        BookingControllerc788df35b14daebdc29d781f7cbb0fbaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings'
 */
        BookingControllerc788df35b14daebdc29d781f7cbb0fbaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingControllerc788df35b14daebdc29d781f7cbb0fba.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    BookingControllerc788df35b14daebdc29d781f7cbb0fba.form = BookingControllerc788df35b14daebdc29d781f7cbb0fbaForm
    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
const BookingControllerba242e6e83ed2b3b86349f97fd392c37 = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, options),
    method: 'get',
})

BookingControllerba242e6e83ed2b3b86349f97fd392c37.definition = {
    methods: ["get","head"],
    url: '/bookings/detail/{groupKey}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
BookingControllerba242e6e83ed2b3b86349f97fd392c37.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { groupKey: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    groupKey: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        groupKey: args.groupKey,
                }

    return BookingControllerba242e6e83ed2b3b86349f97fd392c37.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
BookingControllerba242e6e83ed2b3b86349f97fd392c37.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
BookingControllerba242e6e83ed2b3b86349f97fd392c37.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
    const BookingControllerba242e6e83ed2b3b86349f97fd392c37Form = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
        BookingControllerba242e6e83ed2b3b86349f97fd392c37Form.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/bookings/detail/{groupKey}'
 */
        BookingControllerba242e6e83ed2b3b86349f97fd392c37Form.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingControllerba242e6e83ed2b3b86349f97fd392c37.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    BookingControllerba242e6e83ed2b3b86349f97fd392c37.form = BookingControllerba242e6e83ed2b3b86349f97fd392c37Form
    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
const BookingController4d32bcb4c56627ff469b28780ef98d94 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingController4d32bcb4c56627ff469b28780ef98d94.url(options),
    method: 'get',
})

BookingController4d32bcb4c56627ff469b28780ef98d94.definition = {
    methods: ["get","head"],
    url: '/booking-console',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
BookingController4d32bcb4c56627ff469b28780ef98d94.url = (options?: RouteQueryOptions) => {
    return BookingController4d32bcb4c56627ff469b28780ef98d94.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
BookingController4d32bcb4c56627ff469b28780ef98d94.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BookingController4d32bcb4c56627ff469b28780ef98d94.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
BookingController4d32bcb4c56627ff469b28780ef98d94.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: BookingController4d32bcb4c56627ff469b28780ef98d94.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
    const BookingController4d32bcb4c56627ff469b28780ef98d94Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: BookingController4d32bcb4c56627ff469b28780ef98d94.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
        BookingController4d32bcb4c56627ff469b28780ef98d94Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingController4d32bcb4c56627ff469b28780ef98d94.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::__invoke
 * @see app/Http/Controllers/BookingController.php:33
 * @route '/booking-console'
 */
        BookingController4d32bcb4c56627ff469b28780ef98d94Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BookingController4d32bcb4c56627ff469b28780ef98d94.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    BookingController4d32bcb4c56627ff469b28780ef98d94.form = BookingController4d32bcb4c56627ff469b28780ef98d94Form

/**
* Multiple routes resolve to \App\Http\Controllers\BookingController::BookingController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `BookingController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const BookingController: Record<string, unknown> & {
    printManifest?: typeof printManifest;
    downloadManifestPdf?: typeof downloadManifestPdf;
    printTicket?: typeof printTicket;
    downloadTicketPdf?: typeof downloadTicketPdf;
} = {
    '/bookings': BookingControllerc788df35b14daebdc29d781f7cbb0fba,
    '/bookings/detail/{groupKey}': BookingControllerba242e6e83ed2b3b86349f97fd392c37,
    '/booking-console': BookingController4d32bcb4c56627ff469b28780ef98d94,
}

/**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
export const printManifest = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printManifest.url(args, options),
    method: 'get',
})

printManifest.definition = {
    methods: ["get","head"],
    url: '/bookings/manifest/{groupKey}/print',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
printManifest.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { groupKey: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    groupKey: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        groupKey: args.groupKey,
                }

    return printManifest.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
printManifest.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printManifest.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
printManifest.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printManifest.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
    const printManifestForm = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printManifest.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
        printManifestForm.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printManifest.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::printManifest
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
        printManifestForm.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printManifest.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printManifest.form = printManifestForm
/**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
export const downloadManifestPdf = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadManifestPdf.url(args, options),
    method: 'get',
})

downloadManifestPdf.definition = {
    methods: ["get","head"],
    url: '/bookings/manifest/{groupKey}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
downloadManifestPdf.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { groupKey: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    groupKey: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        groupKey: args.groupKey,
                }

    return downloadManifestPdf.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
downloadManifestPdf.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadManifestPdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
downloadManifestPdf.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadManifestPdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
    const downloadManifestPdfForm = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadManifestPdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
        downloadManifestPdfForm.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadManifestPdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::downloadManifestPdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
        downloadManifestPdfForm.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadManifestPdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadManifestPdf.form = downloadManifestPdfForm
/**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
export const printTicket = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printTicket.url(args, options),
    method: 'get',
})

printTicket.definition = {
    methods: ["get","head"],
    url: '/bookings/ticket/{bookingId}/print',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
printTicket.url = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bookingId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    bookingId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bookingId: args.bookingId,
                }

    return printTicket.definition.url
            .replace('{bookingId}', parsedArgs.bookingId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
printTicket.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printTicket.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
printTicket.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printTicket.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
    const printTicketForm = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: printTicket.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
        printTicketForm.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printTicket.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::printTicket
 * @see app/Http/Controllers/BookingController.php:165
 * @route '/bookings/ticket/{bookingId}/print'
 */
        printTicketForm.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: printTicket.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    printTicket.form = printTicketForm
/**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
export const downloadTicketPdf = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadTicketPdf.url(args, options),
    method: 'get',
})

downloadTicketPdf.definition = {
    methods: ["get","head"],
    url: '/bookings/ticket/{bookingId}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
downloadTicketPdf.url = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bookingId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    bookingId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bookingId: args.bookingId,
                }

    return downloadTicketPdf.definition.url
            .replace('{bookingId}', parsedArgs.bookingId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
downloadTicketPdf.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadTicketPdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
downloadTicketPdf.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadTicketPdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
    const downloadTicketPdfForm = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadTicketPdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
        downloadTicketPdfForm.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadTicketPdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::downloadTicketPdf
 * @see app/Http/Controllers/BookingController.php:140
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
        downloadTicketPdfForm.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadTicketPdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadTicketPdf.form = downloadTicketPdfForm
BookingController.printManifest = printManifest
BookingController.downloadManifestPdf = downloadManifestPdf
BookingController.printTicket = printTicket
BookingController.downloadTicketPdf = downloadTicketPdf

export default BookingController