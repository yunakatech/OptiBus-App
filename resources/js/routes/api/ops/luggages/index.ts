import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
export const submit = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/api/ops/luggages',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
submit.url = (options?: RouteQueryOptions) => {
    return submit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
    const submitForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages'
 */
        submitForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(options),
            method: 'post',
        })
    
    submit.form = submitForm
/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitRaw
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
export const submitRaw = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitRaw.url(options),
    method: 'post',
})

submitRaw.definition = {
    methods: ["post"],
    url: '/api/ops/luggages/raw',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitRaw
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
submitRaw.url = (options?: RouteQueryOptions) => {
    return submitRaw.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submitRaw
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
submitRaw.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitRaw.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitRaw
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
    const submitRawForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submitRaw.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submitRaw
 * @see app/Http/Controllers/Api/OperationsApiController.php:397
 * @route '/api/ops/luggages/raw'
 */
        submitRawForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submitRaw.url(options),
            method: 'post',
        })
    
    submitRaw.form = submitRawForm
const luggages = {
    submit: Object.assign(submit, submit),
submitRaw: Object.assign(submitRaw, submitRaw),
}

export default luggages