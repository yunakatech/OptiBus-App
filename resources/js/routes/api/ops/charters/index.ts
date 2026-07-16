import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:336
 * @route '/api/ops/charters'
 */
export const submit = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/api/ops/charters',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:336
 * @route '/api/ops/charters'
 */
submit.url = (options?: RouteQueryOptions) => {
    return submit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:336
 * @route '/api/ops/charters'
 */
submit.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:336
 * @route '/api/ops/charters'
 */
    const submitForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\OperationsApiController::submit
 * @see app/Http/Controllers/Api/OperationsApiController.php:336
 * @route '/api/ops/charters'
 */
        submitForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(options),
            method: 'post',
        })
    
    submit.form = submitForm
const charters = {
    submit: Object.assign(submit, submit),
}

export default charters