import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
import tracking6cfda0 from './tracking'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:2904
 * @route '/api/admin/luggages'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/api/admin/luggages',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::saveRaw
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages/raw'
 */
export const saveRaw = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveRaw.url(options),
    method: 'post',
})

saveRaw.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/raw',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::saveRaw
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages/raw'
 */
saveRaw.url = (options?: RouteQueryOptions) => {
    return saveRaw.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::saveRaw
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages/raw'
 */
saveRaw.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveRaw.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::saveRaw
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages/raw'
 */
    const saveRawForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: saveRaw.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::saveRaw
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3112
 * @route '/api/admin/luggages/raw'
 */
        saveRawForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: saveRaw.url(options),
            method: 'post',
        })
    
    saveRaw.form = saveRawForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3276
 * @route '/api/admin/luggages/bulk-delete'
 */
export const bulkDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDelete.url(options),
    method: 'post',
})

bulkDelete.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/bulk-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3276
 * @route '/api/admin/luggages/bulk-delete'
 */
bulkDelete.url = (options?: RouteQueryOptions) => {
    return bulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3276
 * @route '/api/admin/luggages/bulk-delete'
 */
bulkDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3276
 * @route '/api/admin/luggages/bulk-delete'
 */
    const bulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkDelete
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3276
 * @route '/api/admin/luggages/bulk-delete'
 */
        bulkDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDelete.url(options),
            method: 'post',
        })
    
    bulkDelete.form = bulkDeleteForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3293
 * @route '/api/admin/luggages/bulk-status'
 */
export const bulkStatus = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkStatus.url(options),
    method: 'post',
})

bulkStatus.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/bulk-status',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3293
 * @route '/api/admin/luggages/bulk-status'
 */
bulkStatus.url = (options?: RouteQueryOptions) => {
    return bulkStatus.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3293
 * @route '/api/admin/luggages/bulk-status'
 */
bulkStatus.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkStatus.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3293
 * @route '/api/admin/luggages/bulk-status'
 */
    const bulkStatusForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkStatus.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::bulkStatus
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3293
 * @route '/api/admin/luggages/bulk-status'
 */
        bulkStatusForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkStatus.url(options),
            method: 'post',
        })
    
    bulkStatus.form = bulkStatusForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3340
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
export const markPaid = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaid.url(args, options),
    method: 'post',
})

markPaid.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-paid',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3340
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
markPaid.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return markPaid.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3340
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
markPaid.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaid.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3340
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
    const markPaidForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markPaid.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markPaid
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3340
 * @route '/api/admin/luggages/{id}/mark-paid'
 */
        markPaidForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markPaid.url(args, options),
            method: 'post',
        })
    
    markPaid.form = markPaidForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3367
 * @route '/api/admin/luggages/{id}/mark-active'
 */
export const markActive = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markActive.url(args, options),
    method: 'post',
})

markActive.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-active',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3367
 * @route '/api/admin/luggages/{id}/mark-active'
 */
markActive.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return markActive.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3367
 * @route '/api/admin/luggages/{id}/mark-active'
 */
markActive.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markActive.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3367
 * @route '/api/admin/luggages/{id}/mark-active'
 */
    const markActiveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markActive.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markActive
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3367
 * @route '/api/admin/luggages/{id}/mark-active'
 */
        markActiveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markActive.url(args, options),
            method: 'post',
        })
    
    markActive.form = markActiveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3395
 * @route '/api/admin/luggages/{id}/mark-done'
 */
export const markDone = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markDone.url(args, options),
    method: 'post',
})

markDone.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-done',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3395
 * @route '/api/admin/luggages/{id}/mark-done'
 */
markDone.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return markDone.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3395
 * @route '/api/admin/luggages/{id}/mark-done'
 */
markDone.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markDone.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3395
 * @route '/api/admin/luggages/{id}/mark-done'
 */
    const markDoneForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markDone.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markDone
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3395
 * @route '/api/admin/luggages/{id}/mark-done'
 */
        markDoneForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markDone.url(args, options),
            method: 'post',
        })
    
    markDone.form = markDoneForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3423
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
export const markCanceled = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markCanceled.url(args, options),
    method: 'post',
})

markCanceled.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/mark-canceled',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3423
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
markCanceled.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return markCanceled.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3423
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
markCanceled.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markCanceled.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3423
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
    const markCanceledForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markCanceled.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::markCanceled
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3423
 * @route '/api/admin/luggages/{id}/mark-canceled'
 */
        markCanceledForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markCanceled.url(args, options),
            method: 'post',
        })
    
    markCanceled.form = markCanceledForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
export const tracking = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tracking.url(args, options),
    method: 'get',
})

tracking.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggages/{id}/tracking',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
tracking.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return tracking.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
tracking.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tracking.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
tracking.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tracking.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
    const trackingForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tracking.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
        trackingForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tracking.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::tracking
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3453
 * @route '/api/admin/luggages/{id}/tracking'
 */
        trackingForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tracking.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    tracking.form = trackingForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3256
 * @route '/api/admin/luggages/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/api/admin/luggages/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3256
 * @route '/api/admin/luggages/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3256
 * @route '/api/admin/luggages/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3256
 * @route '/api/admin/luggages/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3256
 * @route '/api/admin/luggages/{id}'
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
const luggages = {
    index: Object.assign(index, index),
save: Object.assign(save, save),
saveRaw: Object.assign(saveRaw, saveRaw),
bulkDelete: Object.assign(bulkDelete, bulkDelete),
bulkStatus: Object.assign(bulkStatus, bulkStatus),
markPaid: Object.assign(markPaid, markPaid),
markActive: Object.assign(markActive, markActive),
markDone: Object.assign(markDone, markDone),
markCanceled: Object.assign(markCanceled, markCanceled),
tracking: Object.assign(tracking, tracking6cfda0),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default luggages