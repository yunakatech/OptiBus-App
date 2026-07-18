import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
export const print = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})

print.definition = {
    methods: ["get","head"],
    url: '/bookings/ticket/{bookingId}/print',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
print.url = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return print.definition.url
            .replace('{bookingId}', parsedArgs.bookingId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
print.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
print.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: print.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
    const printForm = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: print.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
        printForm.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: print.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings/ticket/{bookingId}/print'
 */
        printForm.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: print.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    print.form = printForm
/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
export const pdf = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/bookings/ticket/{bookingId}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
pdf.url = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return pdf.definition.url
            .replace('{bookingId}', parsedArgs.bookingId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
pdf.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
pdf.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
    const pdfForm = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
        pdfForm.get = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:142
 * @route '/bookings/ticket/{bookingId}/pdf'
 */
        pdfForm.head = (args: { bookingId: string | number } | [bookingId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pdf.form = pdfForm
const ticket = {
    print: Object.assign(print, print),
pdf: Object.assign(pdf, pdf),
}

export default ticket