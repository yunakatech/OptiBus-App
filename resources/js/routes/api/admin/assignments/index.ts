import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/assignments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3398
 * @route '/api/admin/assignments'
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
* @see \App\Http\Controllers\Api\AdminOpsApiController::conflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3460
 * @route '/api/admin/assignments/conflicts'
 */
export const conflicts = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: conflicts.url(options),
    method: 'post',
})

conflicts.definition = {
    methods: ["post"],
    url: '/api/admin/assignments/conflicts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::conflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3460
 * @route '/api/admin/assignments/conflicts'
 */
conflicts.url = (options?: RouteQueryOptions) => {
    return conflicts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::conflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3460
 * @route '/api/admin/assignments/conflicts'
 */
conflicts.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: conflicts.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::conflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3460
 * @route '/api/admin/assignments/conflicts'
 */
    const conflictsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: conflicts.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::conflicts
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3460
 * @route '/api/admin/assignments/conflicts'
 */
        conflictsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: conflicts.url(options),
            method: 'post',
        })
    
    conflicts.form = conflictsForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3484
 * @route '/api/admin/assignments'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/api/admin/assignments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3484
 * @route '/api/admin/assignments'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3484
 * @route '/api/admin/assignments'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3484
 * @route '/api/admin/assignments'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3484
 * @route '/api/admin/assignments'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3675
 * @route '/api/admin/assignments/bulk-delete'
 */
export const bulkDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDelete.url(options),
    method: 'post',
})

bulkDelete.definition = {
    methods: ["post"],
    url: '/api/admin/assignments/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3675
 * @route '/api/admin/assignments/bulk-delete'
 */
bulkDelete.url = (options?: RouteQueryOptions) => {
    return bulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3675
 * @route '/api/admin/assignments/bulk-delete'
 */
bulkDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3675
 * @route '/api/admin/assignments/bulk-delete'
 */
    const bulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3675
 * @route '/api/admin/assignments/bulk-delete'
 */
        bulkDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDelete.url(options),
            method: 'post',
        })
    
    bulkDelete.form = bulkDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3663
 * @route '/api/admin/assignments/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/api/admin/assignments/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3663
 * @route '/api/admin/assignments/{id}'
 */
deleteMethod.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return deleteMethod.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3663
 * @route '/api/admin/assignments/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3663
 * @route '/api/admin/assignments/{id}'
 */
    const deleteMethodForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteMethod.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3663
 * @route '/api/admin/assignments/{id}'
 */
        deleteMethodForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteMethod.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteMethod.form = deleteMethodForm
const assignments = {
    index: Object.assign(index, index),
conflicts: Object.assign(conflicts, conflicts),
save: Object.assign(save, save),
bulkDelete: Object.assign(bulkDelete, bulkDelete),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default assignments