import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\PaymentWebhookController::mayar
 * @see app/Http/Controllers/Api/PaymentWebhookController.php:37
 * @route '/api/webhooks/mayar'
 */
export const mayar = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: mayar.url(options),
    method: 'post',
})

mayar.definition = {
    methods: ["post"],
    url: '/api/webhooks/mayar',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\PaymentWebhookController::mayar
 * @see app/Http/Controllers/Api/PaymentWebhookController.php:37
 * @route '/api/webhooks/mayar'
 */
mayar.url = (options?: RouteQueryOptions) => {
    return mayar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PaymentWebhookController::mayar
 * @see app/Http/Controllers/Api/PaymentWebhookController.php:37
 * @route '/api/webhooks/mayar'
 */
mayar.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: mayar.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\PaymentWebhookController::mayar
 * @see app/Http/Controllers/Api/PaymentWebhookController.php:37
 * @route '/api/webhooks/mayar'
 */
    const mayarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: mayar.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\PaymentWebhookController::mayar
 * @see app/Http/Controllers/Api/PaymentWebhookController.php:37
 * @route '/api/webhooks/mayar'
 */
        mayarForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: mayar.url(options),
            method: 'post',
        })
    
    mayar.form = mayarForm
const PaymentWebhookController = { mayar }

export default PaymentWebhookController