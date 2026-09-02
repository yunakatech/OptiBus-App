import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
export const status = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(args, options),
    method: 'get',
})

status.definition = {
    methods: ["get","head"],
    url: '/api/admin/tenant-deletions/{jobId}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
status.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jobId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    jobId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        jobId: args.jobId,
                }

    return status.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
status.get = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
status.head = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: status.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
    const statusForm = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: status.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
        statusForm.get = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::status
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11903
 * @route '/api/admin/tenant-deletions/{jobId}'
 */
        statusForm.head = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::retry
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11931
 * @route '/api/admin/tenant-deletions/{jobId}/retry'
 */
export const retry = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retry.url(args, options),
    method: 'post',
})

retry.definition = {
    methods: ["post"],
    url: '/api/admin/tenant-deletions/{jobId}/retry',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::retry
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11931
 * @route '/api/admin/tenant-deletions/{jobId}/retry'
 */
retry.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jobId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    jobId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        jobId: args.jobId,
                }

    return retry.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::retry
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11931
 * @route '/api/admin/tenant-deletions/{jobId}/retry'
 */
retry.post = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retry.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::retry
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11931
 * @route '/api/admin/tenant-deletions/{jobId}/retry'
 */
    const retryForm = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: retry.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::retry
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11931
 * @route '/api/admin/tenant-deletions/{jobId}/retry'
 */
        retryForm.post = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: retry.url(args, options),
            method: 'post',
        })
    
    retry.form = retryForm
const tenantDeletions = {
    status: Object.assign(status, status),
retry: Object.assign(retry, retry),
}

export default tenantDeletions