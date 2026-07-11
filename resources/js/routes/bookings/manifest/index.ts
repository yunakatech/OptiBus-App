import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
export const print = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})

print.definition = {
    methods: ["get","head"],
    url: '/bookings/manifest/{groupKey}/print',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
print.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return print.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
print.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
print.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: print.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
    const printForm = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: print.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
        printForm.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: print.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::print
 * @see app/Http/Controllers/BookingController.php:85
 * @route '/bookings/manifest/{groupKey}/print'
 */
        printForm.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
export const pdf = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/bookings/manifest/{groupKey}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
pdf.url = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return pdf.definition.url
            .replace('{groupKey}', parsedArgs.groupKey.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
pdf.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
pdf.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
    const pdfForm = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
        pdfForm.get = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::pdf
 * @see app/Http/Controllers/BookingController.php:104
 * @route '/bookings/manifest/{groupKey}/pdf'
 */
        pdfForm.head = (args: { groupKey: string | number } | [groupKey: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pdf.form = pdfForm
const manifest = {
    print: Object.assign(print, print),
pdf: Object.assign(pdf, pdf),
}

export default manifest