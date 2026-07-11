import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import invoice from './invoice'
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters'
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
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
export const form = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(options),
    method: 'get',
})

form.definition = {
    methods: ["get","head"],
    url: '/charters/form',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
form.url = (options?: RouteQueryOptions) => {
    return form.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
form.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
form.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
    const formForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: form.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
        formForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: form.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/form'
 */
        formForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: form.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    form.form = formForm
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
export const view = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})

view.definition = {
    methods: ["get","head"],
    url: '/charters/view/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
view.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return view.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
view.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
view.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: view.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
    const viewForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: view.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
        viewForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: view.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:20
 * @route '/charters/view/{id}'
 */
        viewForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: view.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    view.form = viewForm
const charters = {
    index: Object.assign(index, index),
form: Object.assign(form, form),
view: Object.assign(view, view),
invoice: Object.assign(invoice, invoice),
}

export default charters