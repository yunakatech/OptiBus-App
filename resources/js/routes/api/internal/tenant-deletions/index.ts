import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from '../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
export const process = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: process.url(options),
    method: 'get',
})

process.definition = {
    methods: ["get","post","head"],
    url: '/api/internal/tenant-deletions/process',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
process.url = (options?: RouteQueryOptions) => {
    return process.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
process.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: process.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
process.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})
/**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
process.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: process.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
    const processForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: process.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
        processForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: process.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
        processForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(options),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Api\AdminOpsApiController::process
 * @see app/Http/Controllers/Api/AdminOpsApiController.php:11756
 * @route '/api/internal/tenant-deletions/process'
 */
        processForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: process.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    process.form = processForm
const tenantDeletions = {
    process: Object.assign(process, process),
}

export default tenantDeletions