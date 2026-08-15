import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../wayfinder'
/**
* @see \App\Http\Controllers\PaymentController::bulk
 * @see app/Http/Controllers/PaymentController.php:145
 * @route '/api/admin/payments/bulk'
 */
export const bulk = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulk.url(options),
    method: 'post',
})

bulk.definition = {
    methods: ["post"],
    url: '/api/admin/payments/bulk',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PaymentController::bulk
 * @see app/Http/Controllers/PaymentController.php:145
 * @route '/api/admin/payments/bulk'
 */
bulk.url = (options?: RouteQueryOptions) => {
    return bulk.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PaymentController::bulk
 * @see app/Http/Controllers/PaymentController.php:145
 * @route '/api/admin/payments/bulk'
 */
bulk.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulk.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PaymentController::bulk
 * @see app/Http/Controllers/PaymentController.php:145
 * @route '/api/admin/payments/bulk'
 */
    const bulkForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulk.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PaymentController::bulk
 * @see app/Http/Controllers/PaymentController.php:145
 * @route '/api/admin/payments/bulk'
 */
        bulkForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulk.url(options),
            method: 'post',
        })
    
    bulk.form = bulkForm
/**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:120
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
 * @see app/Http/Controllers/PaymentController.php:120
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
 * @see app/Http/Controllers/PaymentController.php:120
 * @route '/api/admin/payments/{source}/{id}'
 */
update.post = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:120
 * @route '/api/admin/payments/{source}/{id}'
 */
    const updateForm = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PaymentController::update
 * @see app/Http/Controllers/PaymentController.php:120
 * @route '/api/admin/payments/{source}/{id}'
 */
        updateForm.post = (args: { source: string | number, id: string | number } | [source: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, options),
            method: 'post',
        })
    
    update.form = updateForm
const payments = {
    bulk: Object.assign(bulk, bulk),
update: Object.assign(update, update),
}

export default payments