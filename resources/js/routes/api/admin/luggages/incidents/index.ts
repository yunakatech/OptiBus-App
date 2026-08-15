import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
export const index = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/luggages/{id}/incidents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
index.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return index.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
index.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
index.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
    const indexForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
        indexForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3892
 * @route '/api/admin/luggages/{id}/incidents'
 */
        indexForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::store
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3937
 * @route '/api/admin/luggages/{id}/incidents'
 */
export const store = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/{id}/incidents',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::store
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3937
 * @route '/api/admin/luggages/{id}/incidents'
 */
store.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::store
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3937
 * @route '/api/admin/luggages/{id}/incidents'
 */
store.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::store
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3937
 * @route '/api/admin/luggages/{id}/incidents'
 */
    const storeForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::store
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:3937
 * @route '/api/admin/luggages/{id}/incidents'
 */
        storeForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::update
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4072
 * @route '/api/admin/luggages/incidents/{incidentId}'
 */
export const update = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/api/admin/luggages/incidents/{incidentId}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::update
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4072
 * @route '/api/admin/luggages/incidents/{incidentId}'
 */
update.url = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { incidentId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    incidentId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        incidentId: args.incidentId,
                }

    return update.definition.url
            .replace('{incidentId}', parsedArgs.incidentId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::update
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4072
 * @route '/api/admin/luggages/incidents/{incidentId}'
 */
update.patch = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::update
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4072
 * @route '/api/admin/luggages/incidents/{incidentId}'
 */
    const updateForm = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::update
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4072
 * @route '/api/admin/luggages/incidents/{incidentId}'
 */
        updateForm.patch = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::claim
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4175
 * @route '/api/admin/luggages/incidents/{incidentId}/claim'
 */
export const claim = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: claim.url(args, options),
    method: 'post',
})

claim.definition = {
    methods: ["post"],
    url: '/api/admin/luggages/incidents/{incidentId}/claim',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::claim
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4175
 * @route '/api/admin/luggages/incidents/{incidentId}/claim'
 */
claim.url = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { incidentId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    incidentId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        incidentId: args.incidentId,
                }

    return claim.definition.url
            .replace('{incidentId}', parsedArgs.incidentId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::claim
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4175
 * @route '/api/admin/luggages/incidents/{incidentId}/claim'
 */
claim.post = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: claim.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::claim
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4175
 * @route '/api/admin/luggages/incidents/{incidentId}/claim'
 */
    const claimForm = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: claim.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::claim
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:4175
 * @route '/api/admin/luggages/incidents/{incidentId}/claim'
 */
        claimForm.post = (args: { incidentId: string | number } | [incidentId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: claim.url(args, options),
            method: 'post',
        })
    
    claim.form = claimForm
const incidents = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
claim: Object.assign(claim, claim),
}

export default incidents