import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
const PaymentController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PaymentController.url(options),
    method: 'get',
})

PaymentController.definition = {
    methods: ["get","head"],
    url: '/payments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
PaymentController.url = (options?: RouteQueryOptions) => {
    return PaymentController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
PaymentController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PaymentController.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
PaymentController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: PaymentController.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
    const PaymentControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: PaymentController.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
        PaymentControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PaymentController.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PaymentController::__invoke
 * @see app/Http/Controllers/PaymentController.php:32
 * @route '/payments'
 */
        PaymentControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PaymentController.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    PaymentController.form = PaymentControllerForm
/**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/payments/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PaymentController::exportMethod
 * @see app/Http/Controllers/PaymentController.php:45
 * @route '/payments/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:118
 * @route '/api/admin/payments/{source}/{id}'
 */
export const update = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/api/admin/payments/{source}/{id}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:118
 * @route '/api/admin/payments/{source}/{id}'
 */
update.url = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    source: args[0],
                    id: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        source: args.source,
                                id: args.id,
                }

    return update.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:118
 * @route '/api/admin/payments/{source}/{id}'
 */
update.post = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:118
 * @route '/api/admin/payments/{source}/{id}'
 */
    const updateForm = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:118
 * @route '/api/admin/payments/{source}/{id}'
 */
        updateForm.post = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, options),
            method: 'post',
        })
    
    update.form = updateForm
PaymentController.exportMethod = exportMethod
PaymentController.update = update

export default PaymentController