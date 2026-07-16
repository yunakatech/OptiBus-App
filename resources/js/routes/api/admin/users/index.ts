import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::index
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:5933
 * @route '/api/admin/users'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

save.definition = {
    methods: ["post"],
    url: '/api/admin/users',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
save.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: save.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::save
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6042
 * @route '/api/admin/users'
 */
        saveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url(options),
            method: 'post',
        })
    
    save.form = saveForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::verify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
export const verify = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(args, options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::verify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
verify.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return verify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::verify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
verify.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::verify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
    const verifyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::verify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6257
 * @route '/api/admin/users/{id}/verify'
 */
        verifyForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(args, options),
            method: 'post',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
export const unverify = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unverify.url(args, options),
    method: 'post',
})

unverify.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/unverify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
unverify.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return unverify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
unverify.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unverify.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
    const unverifyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unverify.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::unverify
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6280
 * @route '/api/admin/users/{id}/unverify'
 */
        unverifyForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unverify.url(args, options),
            method: 'post',
        })
    
    unverify.form = unverifyForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::sendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
export const sendVerification = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendVerification.url(args, options),
    method: 'post',
})

sendVerification.definition = {
    methods: ["post"],
    url: '/api/admin/users/{id}/send-verification',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::sendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
sendVerification.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return sendVerification.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::sendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
sendVerification.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendVerification.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::sendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
    const sendVerificationForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendVerification.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::sendVerification
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6305
 * @route '/api/admin/users/{id}/send-verification'
 */
        sendVerificationForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendVerification.url(args, options),
            method: 'post',
        })
    
    sendVerification.form = sendVerificationForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/api/admin/users/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::deleteMethod
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
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
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:6193
 * @route '/api/admin/users/{id}'
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
const users = {
    index: Object.assign(index, index),
save: Object.assign(save, save),
verify: Object.assign(verify, verify),
unverify: Object.assign(unverify, unverify),
sendVerification: Object.assign(sendVerification, sendVerification),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default users