import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
export const charters = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charters.url(options),
    method: 'get',
})

charters.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/charters',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
charters.url = (options?: RouteQueryOptions) => {
    return charters.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
charters.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: charters.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
charters.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: charters.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
    const chartersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: charters.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
        chartersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charters.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/charters'
 */
        chartersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: charters.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    charters.form = chartersForm
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
export const luggages = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggages.url(options),
    method: 'get',
})

luggages.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
luggages.url = (options?: RouteQueryOptions) => {
    return luggages.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
luggages.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: luggages.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
luggages.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: luggages.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
    const luggagesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: luggages.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
        luggagesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggages.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/luggages'
 */
        luggagesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: luggages.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    luggages.form = luggagesForm
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
export const assignments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assignments.url(options),
    method: 'get',
})

assignments.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/assignments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
assignments.url = (options?: RouteQueryOptions) => {
    return assignments.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
assignments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assignments.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
assignments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assignments.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
    const assignmentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: assignments.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
        assignmentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assignments.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/assignments'
 */
        assignmentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: assignments.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    assignments.form = assignmentsForm
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin-ops/flows/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsFlowsController::__invoke
 * @see app/Http/Controllers/AdminOpsFlowsController.php:23
 * @route '/admin-ops/flows/export'
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
const flows = {
    charters: Object.assign(charters, charters),
luggages: Object.assign(luggages, luggages),
assignments: Object.assign(assignments, assignments),
export: Object.assign(exportMethod, exportMethod),
}

export default flows