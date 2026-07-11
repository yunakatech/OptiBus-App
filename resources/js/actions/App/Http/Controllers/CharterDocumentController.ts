import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
export const print = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})

print.definition = {
    methods: ["get","head"],
    url: '/charters/{id}/invoice/print',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
print.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return print.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
print.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: print.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
print.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: print.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
    const printForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: print.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
        printForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: print.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CharterDocumentController::print
 * @see app/Http/Controllers/CharterDocumentController.php:15
 * @route '/charters/{id}/invoice/print'
 */
        printForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
export const pdf = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/charters/{id}/invoice/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
pdf.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return pdf.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
pdf.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
pdf.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
    const pdfForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
        pdfForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CharterDocumentController::pdf
 * @see app/Http/Controllers/CharterDocumentController.php:22
 * @route '/charters/{id}/invoice/pdf'
 */
        pdfForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pdf.form = pdfForm
const CharterDocumentController = { print, pdf }

export default CharterDocumentController