import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11639
 * @route '/api/admin/tenants'
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
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11742
 * @route '/api/admin/tenants'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/api/admin/tenants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11742
 * @route '/api/admin/tenants'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11742
 * @route '/api/admin/tenants'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11742
 * @route '/api/admin/tenants'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11742
 * @route '/api/admin/tenants'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
export const deletionPreview = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deletionPreview.url(args, options),
    method: 'get',
})

deletionPreview.definition = {
    methods: ["get","head"],
    url: '/api/admin/tenants/{id}/deletion-preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
deletionPreview.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return deletionPreview.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
deletionPreview.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deletionPreview.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
deletionPreview.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: deletionPreview.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
    const deletionPreviewForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: deletionPreview.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
        deletionPreviewForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deletionPreview.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deletionPreview
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11830
 * @route '/api/admin/tenants/{id}/deletion-preview'
 */
        deletionPreviewForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: deletionPreview.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    deletionPreview.form = deletionPreviewForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::archive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11843
 * @route '/api/admin/tenants/{id}/archive'
 */
export const archive = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: archive.url(args, options),
    method: 'post',
})

archive.definition = {
    methods: ["post"],
    url: '/api/admin/tenants/{id}/archive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::archive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11843
 * @route '/api/admin/tenants/{id}/archive'
 */
archive.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return archive.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::archive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11843
 * @route '/api/admin/tenants/{id}/archive'
 */
archive.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: archive.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::archive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11843
 * @route '/api/admin/tenants/{id}/archive'
 */
    const archiveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: archive.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::archive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11843
 * @route '/api/admin/tenants/{id}/archive'
 */
        archiveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: archive.url(args, options),
            method: 'post',
        })
    
    archive.form = archiveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::purge
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11859
 * @route '/api/admin/tenants/{id}/purge'
 */
export const purge = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: purge.url(args, options),
    method: 'post',
})

purge.definition = {
    methods: ["post"],
    url: '/api/admin/tenants/{id}/purge',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::purge
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11859
 * @route '/api/admin/tenants/{id}/purge'
 */
purge.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return purge.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::purge
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11859
 * @route '/api/admin/tenants/{id}/purge'
 */
purge.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: purge.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::purge
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11859
 * @route '/api/admin/tenants/{id}/purge'
 */
    const purgeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: purge.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::purge
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11859
 * @route '/api/admin/tenants/{id}/purge'
 */
        purgeForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: purge.url(args, options),
            method: 'post',
        })
    
    purge.form = purgeForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11825
 * @route '/api/admin/tenants/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/api/admin/tenants/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11825
 * @route '/api/admin/tenants/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11825
 * @route '/api/admin/tenants/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11825
 * @route '/api/admin/tenants/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11825
 * @route '/api/admin/tenants/{id}'
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
const tenants = {
    index: Object.assign(index, index),
save: Object.assign(save, save),
deletionPreview: Object.assign(deletionPreview, deletionPreview),
archive: Object.assign(archive, archive),
purge: Object.assign(purge, purge),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default tenants