import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
const AdminOpsController09c49cc81052ab7bc3ccda515af769ef = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
    method: 'get',
})

AdminOpsController09c49cc81052ab7bc3ccda515af769ef.definition = {
    methods: ["get","head"],
    url: '/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url = (options?: RouteQueryOptions) => {
    return AdminOpsController09c49cc81052ab7bc3ccda515af769ef.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
    const AdminOpsController09c49cc81052ab7bc3ccda515af769efForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
        AdminOpsController09c49cc81052ab7bc3ccda515af769efForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/report'
 */
        AdminOpsController09c49cc81052ab7bc3ccda515af769efForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController09c49cc81052ab7bc3ccda515af769ef.form = AdminOpsController09c49cc81052ab7bc3ccda515af769efForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
const AdminOpsController58ce3b21459752ee73930d924bf98aec = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
    method: 'get',
})

AdminOpsController58ce3b21459752ee73930d924bf98aec.definition = {
    methods: ["get","head"],
    url: '/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.url = (options?: RouteQueryOptions) => {
    return AdminOpsController58ce3b21459752ee73930d924bf98aec.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
    const AdminOpsController58ce3b21459752ee73930d924bf98aecForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
        AdminOpsController58ce3b21459752ee73930d924bf98aecForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/reports'
 */
        AdminOpsController58ce3b21459752ee73930d924bf98aecForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController58ce3b21459752ee73930d924bf98aec.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController58ce3b21459752ee73930d924bf98aec.form = AdminOpsController58ce3b21459752ee73930d924bf98aecForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
const AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url(options),
    method: 'get',
})

AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.definition = {
    methods: ["get","head"],
    url: '/admin-ops',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
    const AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
        AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops'
 */
        AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a.form = AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8aForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
const AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url(options),
    method: 'get',
})

AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.definition = {
    methods: ["get","head"],
    url: '/admin-ops/rute-induk',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
    const AdminOpsControllerd6b696a18f84954af2f870bdc4c4440cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
        AdminOpsControllerd6b696a18f84954af2f870bdc4c4440cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/rute-induk'
 */
        AdminOpsControllerd6b696a18f84954af2f870bdc4c4440cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c.form = AdminOpsControllerd6b696a18f84954af2f870bdc4c4440cForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
const AdminOpsController61becdfa35a80a139a730da26818f83d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController61becdfa35a80a139a730da26818f83d.url(options),
    method: 'get',
})

AdminOpsController61becdfa35a80a139a730da26818f83d.definition = {
    methods: ["get","head"],
    url: '/admin-ops/jadwal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
AdminOpsController61becdfa35a80a139a730da26818f83d.url = (options?: RouteQueryOptions) => {
    return AdminOpsController61becdfa35a80a139a730da26818f83d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
AdminOpsController61becdfa35a80a139a730da26818f83d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController61becdfa35a80a139a730da26818f83d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
AdminOpsController61becdfa35a80a139a730da26818f83d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController61becdfa35a80a139a730da26818f83d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
    const AdminOpsController61becdfa35a80a139a730da26818f83dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController61becdfa35a80a139a730da26818f83d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
        AdminOpsController61becdfa35a80a139a730da26818f83dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController61becdfa35a80a139a730da26818f83d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/jadwal'
 */
        AdminOpsController61becdfa35a80a139a730da26818f83dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController61becdfa35a80a139a730da26818f83d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController61becdfa35a80a139a730da26818f83d.form = AdminOpsController61becdfa35a80a139a730da26818f83dForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
const AdminOpsController1b28ae9b831be97a4014057cbbfc9d98 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url(options),
    method: 'get',
})

AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.definition = {
    methods: ["get","head"],
    url: '/admin-ops/driver',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url = (options?: RouteQueryOptions) => {
    return AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
    const AdminOpsController1b28ae9b831be97a4014057cbbfc9d98Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
        AdminOpsController1b28ae9b831be97a4014057cbbfc9d98Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/driver'
 */
        AdminOpsController1b28ae9b831be97a4014057cbbfc9d98Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController1b28ae9b831be97a4014057cbbfc9d98.form = AdminOpsController1b28ae9b831be97a4014057cbbfc9d98Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
const AdminOpsController268eab90dd644141dcd731c9316ac9a6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url(options),
    method: 'get',
})

AdminOpsController268eab90dd644141dcd731c9316ac9a6.definition = {
    methods: ["get","head"],
    url: '/admin-ops/tarif-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
AdminOpsController268eab90dd644141dcd731c9316ac9a6.url = (options?: RouteQueryOptions) => {
    return AdminOpsController268eab90dd644141dcd731c9316ac9a6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
AdminOpsController268eab90dd644141dcd731c9316ac9a6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
AdminOpsController268eab90dd644141dcd731c9316ac9a6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
    const AdminOpsController268eab90dd644141dcd731c9316ac9a6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
        AdminOpsController268eab90dd644141dcd731c9316ac9a6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/tarif-bagasi'
 */
        AdminOpsController268eab90dd644141dcd731c9316ac9a6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController268eab90dd644141dcd731c9316ac9a6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController268eab90dd644141dcd731c9316ac9a6.form = AdminOpsController268eab90dd644141dcd731c9316ac9a6Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
const AdminOpsController5490b1b73eb7dace1582289d472ca4bc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url(options),
    method: 'get',
})

AdminOpsController5490b1b73eb7dace1582289d472ca4bc.definition = {
    methods: ["get","head"],
    url: '/admin-ops/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url = (options?: RouteQueryOptions) => {
    return AdminOpsController5490b1b73eb7dace1582289d472ca4bc.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
AdminOpsController5490b1b73eb7dace1582289d472ca4bc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
AdminOpsController5490b1b73eb7dace1582289d472ca4bc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
    const AdminOpsController5490b1b73eb7dace1582289d472ca4bcForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
        AdminOpsController5490b1b73eb7dace1582289d472ca4bcForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/segments'
 */
        AdminOpsController5490b1b73eb7dace1582289d472ca4bcForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController5490b1b73eb7dace1582289d472ca4bc.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController5490b1b73eb7dace1582289d472ca4bc.form = AdminOpsController5490b1b73eb7dace1582289d472ca4bcForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
const AdminOpsController6bb0c32a0adfcc244c2b15b346e98261 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url(options),
    method: 'get',
})

AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.definition = {
    methods: ["get","head"],
    url: '/admin-ops/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url = (options?: RouteQueryOptions) => {
    return AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
    const AdminOpsController6bb0c32a0adfcc244c2b15b346e98261Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
        AdminOpsController6bb0c32a0adfcc244c2b15b346e98261Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/customers'
 */
        AdminOpsController6bb0c32a0adfcc244c2b15b346e98261Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController6bb0c32a0adfcc244c2b15b346e98261.form = AdminOpsController6bb0c32a0adfcc244c2b15b346e98261Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
const AdminOpsController162e149704fa18678ce2111e121c28b5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController162e149704fa18678ce2111e121c28b5.url(options),
    method: 'get',
})

AdminOpsController162e149704fa18678ce2111e121c28b5.definition = {
    methods: ["get","head"],
    url: '/admin-ops/kategori-armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
AdminOpsController162e149704fa18678ce2111e121c28b5.url = (options?: RouteQueryOptions) => {
    return AdminOpsController162e149704fa18678ce2111e121c28b5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
AdminOpsController162e149704fa18678ce2111e121c28b5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController162e149704fa18678ce2111e121c28b5.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
AdminOpsController162e149704fa18678ce2111e121c28b5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController162e149704fa18678ce2111e121c28b5.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
    const AdminOpsController162e149704fa18678ce2111e121c28b5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController162e149704fa18678ce2111e121c28b5.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
        AdminOpsController162e149704fa18678ce2111e121c28b5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController162e149704fa18678ce2111e121c28b5.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada'
 */
        AdminOpsController162e149704fa18678ce2111e121c28b5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController162e149704fa18678ce2111e121c28b5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController162e149704fa18678ce2111e121c28b5.form = AdminOpsController162e149704fa18678ce2111e121c28b5Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
const AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, options),
    method: 'get',
})

AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.definition = {
    methods: ["get","head"],
    url: '/admin-ops/kategori-armada/layout/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
    const AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
        AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/kategori-armada/layout/{id}'
 */
        AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93.form = AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
const AdminOpsControllered15ae687ed98945f3038854809fea6a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllered15ae687ed98945f3038854809fea6a.url(options),
    method: 'get',
})

AdminOpsControllered15ae687ed98945f3038854809fea6a.definition = {
    methods: ["get","head"],
    url: '/admin-ops/armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
AdminOpsControllered15ae687ed98945f3038854809fea6a.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllered15ae687ed98945f3038854809fea6a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
AdminOpsControllered15ae687ed98945f3038854809fea6a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllered15ae687ed98945f3038854809fea6a.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
AdminOpsControllered15ae687ed98945f3038854809fea6a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllered15ae687ed98945f3038854809fea6a.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
    const AdminOpsControllered15ae687ed98945f3038854809fea6aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllered15ae687ed98945f3038854809fea6a.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
        AdminOpsControllered15ae687ed98945f3038854809fea6aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllered15ae687ed98945f3038854809fea6a.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada'
 */
        AdminOpsControllered15ae687ed98945f3038854809fea6aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllered15ae687ed98945f3038854809fea6a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllered15ae687ed98945f3038854809fea6a.form = AdminOpsControllered15ae687ed98945f3038854809fea6aForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
const AdminOpsController73d2bb54498445b19d208a2911063d10 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, options),
    method: 'get',
})

AdminOpsController73d2bb54498445b19d208a2911063d10.definition = {
    methods: ["get","head"],
    url: '/admin-ops/armada/view/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
AdminOpsController73d2bb54498445b19d208a2911063d10.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return AdminOpsController73d2bb54498445b19d208a2911063d10.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
AdminOpsController73d2bb54498445b19d208a2911063d10.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
AdminOpsController73d2bb54498445b19d208a2911063d10.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
    const AdminOpsController73d2bb54498445b19d208a2911063d10Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
        AdminOpsController73d2bb54498445b19d208a2911063d10Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/armada/view/{id}'
 */
        AdminOpsController73d2bb54498445b19d208a2911063d10Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController73d2bb54498445b19d208a2911063d10.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController73d2bb54498445b19d208a2911063d10.form = AdminOpsController73d2bb54498445b19d208a2911063d10Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
const AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url(options),
    method: 'get',
})

AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.definition = {
    methods: ["get","head"],
    url: '/admin-ops/pool',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url = (options?: RouteQueryOptions) => {
    return AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
    const AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
        AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/pool'
 */
        AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53.form = AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
const AdminOpsController52abacc5717cb8f012a885147b59fff1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController52abacc5717cb8f012a885147b59fff1.url(options),
    method: 'get',
})

AdminOpsController52abacc5717cb8f012a885147b59fff1.definition = {
    methods: ["get","head"],
    url: '/admin-ops/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
AdminOpsController52abacc5717cb8f012a885147b59fff1.url = (options?: RouteQueryOptions) => {
    return AdminOpsController52abacc5717cb8f012a885147b59fff1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
AdminOpsController52abacc5717cb8f012a885147b59fff1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController52abacc5717cb8f012a885147b59fff1.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
AdminOpsController52abacc5717cb8f012a885147b59fff1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController52abacc5717cb8f012a885147b59fff1.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
    const AdminOpsController52abacc5717cb8f012a885147b59fff1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController52abacc5717cb8f012a885147b59fff1.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
        AdminOpsController52abacc5717cb8f012a885147b59fff1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController52abacc5717cb8f012a885147b59fff1.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/users'
 */
        AdminOpsController52abacc5717cb8f012a885147b59fff1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController52abacc5717cb8f012a885147b59fff1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController52abacc5717cb8f012a885147b59fff1.form = AdminOpsController52abacc5717cb8f012a885147b59fff1Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
const AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url(options),
    method: 'get',
})

AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.definition = {
    methods: ["get","head"],
    url: '/admin-ops/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url = (options?: RouteQueryOptions) => {
    return AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
    const AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
        AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/roles'
 */
        AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1.form = AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
const AdminOpsControlleraed85c6578d97648f2274e87439ec518 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url(options),
    method: 'get',
})

AdminOpsControlleraed85c6578d97648f2274e87439ec518.definition = {
    methods: ["get","head"],
    url: '/admin-ops/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
AdminOpsControlleraed85c6578d97648f2274e87439ec518.url = (options?: RouteQueryOptions) => {
    return AdminOpsControlleraed85c6578d97648f2274e87439ec518.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
AdminOpsControlleraed85c6578d97648f2274e87439ec518.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
AdminOpsControlleraed85c6578d97648f2274e87439ec518.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
    const AdminOpsControlleraed85c6578d97648f2274e87439ec518Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
        AdminOpsControlleraed85c6578d97648f2274e87439ec518Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/logs'
 */
        AdminOpsControlleraed85c6578d97648f2274e87439ec518Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleraed85c6578d97648f2274e87439ec518.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControlleraed85c6578d97648f2274e87439ec518.form = AdminOpsControlleraed85c6578d97648f2274e87439ec518Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
const AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url(options),
    method: 'get',
})

AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.definition = {
    methods: ["get","head"],
    url: '/admin-ops/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url = (options?: RouteQueryOptions) => {
    return AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
    const AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
        AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:31
 * @route '/admin-ops/reports'
 */
        AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4.form = AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4Form

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsController::AdminOpsController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsController = {
    '/report': AdminOpsController09c49cc81052ab7bc3ccda515af769ef,
    '/reports': AdminOpsController58ce3b21459752ee73930d924bf98aec,
    '/admin-ops': AdminOpsControllere5daf0beee3caba68d31a8404fa7fa8a,
    '/admin-ops/rute-induk': AdminOpsControllerd6b696a18f84954af2f870bdc4c4440c,
    '/admin-ops/jadwal': AdminOpsController61becdfa35a80a139a730da26818f83d,
    '/admin-ops/driver': AdminOpsController1b28ae9b831be97a4014057cbbfc9d98,
    '/admin-ops/tarif-bagasi': AdminOpsController268eab90dd644141dcd731c9316ac9a6,
    '/admin-ops/segments': AdminOpsController5490b1b73eb7dace1582289d472ca4bc,
    '/admin-ops/customers': AdminOpsController6bb0c32a0adfcc244c2b15b346e98261,
    '/admin-ops/kategori-armada': AdminOpsController162e149704fa18678ce2111e121c28b5,
    '/admin-ops/kategori-armada/layout/{id}': AdminOpsControllera7d3f57862e1fbe3ca4c66230a1ddb93,
    '/admin-ops/armada': AdminOpsControllered15ae687ed98945f3038854809fea6a,
    '/admin-ops/armada/view/{id}': AdminOpsController73d2bb54498445b19d208a2911063d10,
    '/admin-ops/pool': AdminOpsController5d7e473708ef78edf9e5a82a1ca3fb53,
    '/admin-ops/users': AdminOpsController52abacc5717cb8f012a885147b59fff1,
    '/admin-ops/roles': AdminOpsController8eab261f8bde65ee3ec0e60ca635b6c1,
    '/admin-ops/logs': AdminOpsControlleraed85c6578d97648f2274e87439ec518,
    '/admin-ops/reports': AdminOpsController2f5630c46bb9918f2ed309f050c9d3f4,
}

export default AdminOpsController