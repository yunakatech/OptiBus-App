import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
const RedirectController2152ea92a7834749e22b9a3c349cc951 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'get',
})

RedirectController2152ea92a7834749e22b9a3c349cc951.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/charters',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.url = (options?: RouteQueryOptions) => {
    return RedirectController2152ea92a7834749e22b9a3c349cc951.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
RedirectController2152ea92a7834749e22b9a3c349cc951.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
    const RedirectController2152ea92a7834749e22b9a3c349cc951Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/charters'
 */
        RedirectController2152ea92a7834749e22b9a3c349cc951Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController2152ea92a7834749e22b9a3c349cc951.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController2152ea92a7834749e22b9a3c349cc951.form = RedirectController2152ea92a7834749e22b9a3c349cc951Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
const RedirectController4d64ae0cc5019c55b1dc223e2d8ced16 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'get',
})

RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/luggages',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url = (options?: RouteQueryOptions) => {
    return RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
    const RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggages'
 */
        RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController4d64ae0cc5019c55b1dc223e2d8ced16.form = RedirectController4d64ae0cc5019c55b1dc223e2d8ced16Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
const RedirectController03ed0b59e72c52047adde5a9ffc3d0b8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'get',
})

RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/luggage-services',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url = (options?: RouteQueryOptions) => {
    return RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
    const RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/luggage-services'
 */
        RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController03ed0b59e72c52047adde5a9ffc3d0b8.form = RedirectController03ed0b59e72c52047adde5a9ffc3d0b8Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
const RedirectController9ae2fb1fbabd60f580d8111d7b296cb5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'get',
})

RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/customers',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url = (options?: RouteQueryOptions) => {
    return RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
    const RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customers'
 */
        RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController9ae2fb1fbabd60f580d8111d7b296cb5.form = RedirectController9ae2fb1fbabd60f580d8111d7b296cb5Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
const RedirectController66596e169063f2e62f0fb04d205fd131 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'get',
})

RedirectController66596e169063f2e62f0fb04d205fd131.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/admin-ops/customers',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.url = (options?: RouteQueryOptions) => {
    return RedirectController66596e169063f2e62f0fb04d205fd131.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
RedirectController66596e169063f2e62f0fb04d205fd131.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
    const RedirectController66596e169063f2e62f0fb04d205fd131Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/customers'
 */
        RedirectController66596e169063f2e62f0fb04d205fd131Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController66596e169063f2e62f0fb04d205fd131.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController66596e169063f2e62f0fb04d205fd131.form = RedirectController66596e169063f2e62f0fb04d205fd131Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
const RedirectController260709a2994b5885663c3ed896eb49e4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'get',
})

RedirectController260709a2994b5885663c3ed896eb49e4.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/routes',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.url = (options?: RouteQueryOptions) => {
    return RedirectController260709a2994b5885663c3ed896eb49e4.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
RedirectController260709a2994b5885663c3ed896eb49e4.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
    const RedirectController260709a2994b5885663c3ed896eb49e4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/routes'
 */
        RedirectController260709a2994b5885663c3ed896eb49e4Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController260709a2994b5885663c3ed896eb49e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController260709a2994b5885663c3ed896eb49e4.form = RedirectController260709a2994b5885663c3ed896eb49e4Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
const RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'get',
})

RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/schedules',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url = (options?: RouteQueryOptions) => {
    return RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
    const RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/schedules'
 */
        RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8.form = RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
const RedirectController781825890405df57051e229f96052fea = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'get',
})

RedirectController781825890405df57051e229f96052fea.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/drivers',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.url = (options?: RouteQueryOptions) => {
    return RedirectController781825890405df57051e229f96052fea.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
RedirectController781825890405df57051e229f96052fea.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController781825890405df57051e229f96052fea.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
    const RedirectController781825890405df57051e229f96052feaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController781825890405df57051e229f96052fea.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/drivers'
 */
        RedirectController781825890405df57051e229f96052feaForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController781825890405df57051e229f96052fea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController781825890405df57051e229f96052fea.form = RedirectController781825890405df57051e229f96052feaForm
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
const RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'get',
})

RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/services',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url = (options?: RouteQueryOptions) => {
    return RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
    const RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/services'
 */
        RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca.form = RedirectControllerf6c7005edefd2be0ddedddd1aa0300caForm
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
const RedirectController659d9d735bff082ab31103cede9d51ef = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'get',
})

RedirectController659d9d735bff082ab31103cede9d51ef.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/segments',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.url = (options?: RouteQueryOptions) => {
    return RedirectController659d9d735bff082ab31103cede9d51ef.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
RedirectController659d9d735bff082ab31103cede9d51ef.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
    const RedirectController659d9d735bff082ab31103cede9d51efForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/segments'
 */
        RedirectController659d9d735bff082ab31103cede9d51efForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController659d9d735bff082ab31103cede9d51ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController659d9d735bff082ab31103cede9d51ef.form = RedirectController659d9d735bff082ab31103cede9d51efForm
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
const RedirectController90949abaf965b6dea7349dc727227047 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'get',
})

RedirectController90949abaf965b6dea7349dc727227047.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/units',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.url = (options?: RouteQueryOptions) => {
    return RedirectController90949abaf965b6dea7349dc727227047.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
RedirectController90949abaf965b6dea7349dc727227047.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController90949abaf965b6dea7349dc727227047.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
    const RedirectController90949abaf965b6dea7349dc727227047Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController90949abaf965b6dea7349dc727227047.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/units'
 */
        RedirectController90949abaf965b6dea7349dc727227047Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController90949abaf965b6dea7349dc727227047.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController90949abaf965b6dea7349dc727227047.form = RedirectController90949abaf965b6dea7349dc727227047Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
const RedirectController23f7cc8aa9e139176d67d1f0c86c23d1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'get',
})

RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/armadas',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url = (options?: RouteQueryOptions) => {
    return RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
    const RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/armadas'
 */
        RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController23f7cc8aa9e139176d67d1f0c86c23d1.form = RedirectController23f7cc8aa9e139176d67d1f0c86c23d1Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
const RedirectController44802a817abd6dbda07b4f9df9a3b028 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'get',
})

RedirectController44802a817abd6dbda07b4f9df9a3b028.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/admin-ops/pool',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.url = (options?: RouteQueryOptions) => {
    return RedirectController44802a817abd6dbda07b4f9df9a3b028.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
RedirectController44802a817abd6dbda07b4f9df9a3b028.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
    const RedirectController44802a817abd6dbda07b4f9df9a3b028Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/admin-ops/pool'
 */
        RedirectController44802a817abd6dbda07b4f9df9a3b028Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController44802a817abd6dbda07b4f9df9a3b028.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController44802a817abd6dbda07b4f9df9a3b028.form = RedirectController44802a817abd6dbda07b4f9df9a3b028Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
const RedirectControllerde7b92f5d57ab3be25571f27f05793f8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'get',
})

RedirectControllerde7b92f5d57ab3be25571f27f05793f8.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url = (options?: RouteQueryOptions) => {
    return RedirectControllerde7b92f5d57ab3be25571f27f05793f8.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
RedirectControllerde7b92f5d57ab3be25571f27f05793f8.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
    const RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/users'
 */
        RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectControllerde7b92f5d57ab3be25571f27f05793f8.form = RedirectControllerde7b92f5d57ab3be25571f27f05793f8Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
const RedirectControllerf32b5bd5940752871d5bf97794f0c32e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'get',
})

RedirectControllerf32b5bd5940752871d5bf97794f0c32e.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/reports',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url = (options?: RouteQueryOptions) => {
    return RedirectControllerf32b5bd5940752871d5bf97794f0c32e.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
RedirectControllerf32b5bd5940752871d5bf97794f0c32e.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
    const RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/reports'
 */
        RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerf32b5bd5940752871d5bf97794f0c32e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectControllerf32b5bd5940752871d5bf97794f0c32e.form = RedirectControllerf32b5bd5940752871d5bf97794f0c32eForm
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
const RedirectController36ea9bf0b4b78857b3405fc079a379f7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'get',
})

RedirectController36ea9bf0b4b78857b3405fc079a379f7.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/flows',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.url = (options?: RouteQueryOptions) => {
    return RedirectController36ea9bf0b4b78857b3405fc079a379f7.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
RedirectController36ea9bf0b4b78857b3405fc079a379f7.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
    const RedirectController36ea9bf0b4b78857b3405fc079a379f7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/flows'
 */
        RedirectController36ea9bf0b4b78857b3405fc079a379f7Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController36ea9bf0b4b78857b3405fc079a379f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController36ea9bf0b4b78857b3405fc079a379f7.form = RedirectController36ea9bf0b4b78857b3405fc079a379f7Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
const RedirectController8198e40876d6199954ddd262016602f9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'get',
})

RedirectController8198e40876d6199954ddd262016602f9.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/master',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.url = (options?: RouteQueryOptions) => {
    return RedirectController8198e40876d6199954ddd262016602f9.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
RedirectController8198e40876d6199954ddd262016602f9.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController8198e40876d6199954ddd262016602f9.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
    const RedirectController8198e40876d6199954ddd262016602f9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController8198e40876d6199954ddd262016602f9.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/master'
 */
        RedirectController8198e40876d6199954ddd262016602f9Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController8198e40876d6199954ddd262016602f9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController8198e40876d6199954ddd262016602f9.form = RedirectController8198e40876d6199954ddd262016602f9Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
const RedirectController03980c8efc571b632a33a0d77e855b85 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'get',
})

RedirectController03980c8efc571b632a33a0d77e855b85.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/customer-bagasi',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.url = (options?: RouteQueryOptions) => {
    return RedirectController03980c8efc571b632a33a0d77e855b85.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
RedirectController03980c8efc571b632a33a0d77e855b85.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
    const RedirectController03980c8efc571b632a33a0d77e855b85Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-bagasi'
 */
        RedirectController03980c8efc571b632a33a0d77e855b85Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController03980c8efc571b632a33a0d77e855b85.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController03980c8efc571b632a33a0d77e855b85.form = RedirectController03980c8efc571b632a33a0d77e855b85Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
const RedirectController440dff64d7e51fd66afdae46993f6f7a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'get',
})

RedirectController440dff64d7e51fd66afdae46993f6f7a.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/customer-charter',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.url = (options?: RouteQueryOptions) => {
    return RedirectController440dff64d7e51fd66afdae46993f6f7a.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
RedirectController440dff64d7e51fd66afdae46993f6f7a.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
    const RedirectController440dff64d7e51fd66afdae46993f6f7aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/customer-charter'
 */
        RedirectController440dff64d7e51fd66afdae46993f6f7aForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController440dff64d7e51fd66afdae46993f6f7a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController440dff64d7e51fd66afdae46993f6f7a.form = RedirectController440dff64d7e51fd66afdae46993f6f7aForm
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
const RedirectController9421e7328ac4e52ca0b754990c419281 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'get',
})

RedirectController9421e7328ac4e52ca0b754990c419281.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/rute-carter',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.url = (options?: RouteQueryOptions) => {
    return RedirectController9421e7328ac4e52ca0b754990c419281.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
RedirectController9421e7328ac4e52ca0b754990c419281.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
    const RedirectController9421e7328ac4e52ca0b754990c419281Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/rute-carter'
 */
        RedirectController9421e7328ac4e52ca0b754990c419281Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController9421e7328ac4e52ca0b754990c419281.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController9421e7328ac4e52ca0b754990c419281.form = RedirectController9421e7328ac4e52ca0b754990c419281Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
const RedirectControllerb4aecc9000a70e3830a638bd26f64415 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'get',
})

RedirectControllerb4aecc9000a70e3830a638bd26f64415.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/admin/logs',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.url = (options?: RouteQueryOptions) => {
    return RedirectControllerb4aecc9000a70e3830a638bd26f64415.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
RedirectControllerb4aecc9000a70e3830a638bd26f64415.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
    const RedirectControllerb4aecc9000a70e3830a638bd26f64415Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/admin/logs'
 */
        RedirectControllerb4aecc9000a70e3830a638bd26f64415Form.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectControllerb4aecc9000a70e3830a638bd26f64415.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectControllerb4aecc9000a70e3830a638bd26f64415.form = RedirectControllerb4aecc9000a70e3830a638bd26f64415Form
    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
const RedirectController4b87d2df7e3aa853f6720faea796e36c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'get',
})

RedirectController4b87d2df7e3aa853f6720faea796e36c.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/settings',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.url = (options?: RouteQueryOptions) => {
    return RedirectController4b87d2df7e3aa853f6720faea796e36c.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'get',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'head',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'post',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'put',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'patch',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'delete',
})
/**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
RedirectController4b87d2df7e3aa853f6720faea796e36c.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
    method: 'options',
})

    /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
    const RedirectController4b87d2df7e3aa853f6720faea796e36cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
        method: 'get',
    })

            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url(options),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \Illuminate\Routing\RedirectController::__invoke
 * @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
 * @route '/settings'
 */
        RedirectController4b87d2df7e3aa853f6720faea796e36cForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: RedirectController4b87d2df7e3aa853f6720faea796e36c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'OPTIONS',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    RedirectController4b87d2df7e3aa853f6720faea796e36c.form = RedirectController4b87d2df7e3aa853f6720faea796e36cForm

/**
* Multiple routes resolve to \Illuminate\Routing\RedirectController::RedirectController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `RedirectController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const RedirectController = {
    '/admin/charters': RedirectController2152ea92a7834749e22b9a3c349cc951,
    '/admin/luggages': RedirectController4d64ae0cc5019c55b1dc223e2d8ced16,
    '/admin/luggage-services': RedirectController03ed0b59e72c52047adde5a9ffc3d0b8,
    '/admin/customers': RedirectController9ae2fb1fbabd60f580d8111d7b296cb5,
    '/admin/admin-ops/customers': RedirectController66596e169063f2e62f0fb04d205fd131,
    '/admin/routes': RedirectController260709a2994b5885663c3ed896eb49e4,
    '/admin/schedules': RedirectControllerbfe430ac67b7d9113c504c70a6b3abe8,
    '/admin/drivers': RedirectController781825890405df57051e229f96052fea,
    '/admin/services': RedirectControllerf6c7005edefd2be0ddedddd1aa0300ca,
    '/admin/segments': RedirectController659d9d735bff082ab31103cede9d51ef,
    '/admin/units': RedirectController90949abaf965b6dea7349dc727227047,
    '/admin/armadas': RedirectController23f7cc8aa9e139176d67d1f0c86c23d1,
    '/admin/admin-ops/pool': RedirectController44802a817abd6dbda07b4f9df9a3b028,
    '/admin/users': RedirectControllerde7b92f5d57ab3be25571f27f05793f8,
    '/admin/reports': RedirectControllerf32b5bd5940752871d5bf97794f0c32e,
    '/admin/flows': RedirectController36ea9bf0b4b78857b3405fc079a379f7,
    '/admin/master': RedirectController8198e40876d6199954ddd262016602f9,
    '/admin/customer-bagasi': RedirectController03980c8efc571b632a33a0d77e855b85,
    '/admin/customer-charter': RedirectController440dff64d7e51fd66afdae46993f6f7a,
    '/admin/rute-carter': RedirectController9421e7328ac4e52ca0b754990c419281,
    '/admin/logs': RedirectControllerb4aecc9000a70e3830a638bd26f64415,
    '/settings': RedirectController4b87d2df7e3aa853f6720faea796e36c,
}

export default RedirectController