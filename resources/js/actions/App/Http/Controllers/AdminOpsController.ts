import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url = (options?: RouteQueryOptions) => {
    return AdminOpsController09c49cc81052ab7bc3ccda515af769ef.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/report'
 */
AdminOpsController09c49cc81052ab7bc3ccda515af769ef.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/report'
 */
    const AdminOpsController09c49cc81052ab7bc3ccda515af769efForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/report'
 */
        AdminOpsController09c49cc81052ab7bc3ccda515af769efForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController09c49cc81052ab7bc3ccda515af769ef.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
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
 * @see app/Http/Controllers/AdminOpsController.php:32
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.url = (options?: RouteQueryOptions) => {
    return AdminOpsController58ce3b21459752ee73930d924bf98aec.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/reports'
 */
AdminOpsController58ce3b21459752ee73930d924bf98aec.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/reports'
 */
    const AdminOpsController58ce3b21459752ee73930d924bf98aecForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/reports'
 */
        AdminOpsController58ce3b21459752ee73930d924bf98aecForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController58ce3b21459752ee73930d924bf98aec.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
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
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
const AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url(options),
    method: 'get',
})

AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.definition = {
    methods: ["get","head"],
    url: '/settings/rute-induk',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
    const AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dadForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
        AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dadForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/rute-induk'
 */
        AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dadForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad.form = AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dadForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
const AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url(options),
    method: 'get',
})

AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.definition = {
    methods: ["get","head"],
    url: '/settings/jadwal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
    const AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
        AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/jadwal'
 */
        AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6.form = AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
const AdminOpsController29d56f4279284c9126127f89b74091e6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController29d56f4279284c9126127f89b74091e6.url(options),
    method: 'get',
})

AdminOpsController29d56f4279284c9126127f89b74091e6.definition = {
    methods: ["get","head"],
    url: '/settings/driver',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
AdminOpsController29d56f4279284c9126127f89b74091e6.url = (options?: RouteQueryOptions) => {
    return AdminOpsController29d56f4279284c9126127f89b74091e6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
AdminOpsController29d56f4279284c9126127f89b74091e6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController29d56f4279284c9126127f89b74091e6.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
AdminOpsController29d56f4279284c9126127f89b74091e6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController29d56f4279284c9126127f89b74091e6.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
    const AdminOpsController29d56f4279284c9126127f89b74091e6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController29d56f4279284c9126127f89b74091e6.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
        AdminOpsController29d56f4279284c9126127f89b74091e6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController29d56f4279284c9126127f89b74091e6.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/driver'
 */
        AdminOpsController29d56f4279284c9126127f89b74091e6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController29d56f4279284c9126127f89b74091e6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController29d56f4279284c9126127f89b74091e6.form = AdminOpsController29d56f4279284c9126127f89b74091e6Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
const AdminOpsControllerd8ea95465d19058c1124a4a676403642 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url(options),
    method: 'get',
})

AdminOpsControllerd8ea95465d19058c1124a4a676403642.definition = {
    methods: ["get","head"],
    url: '/settings/tarif-bagasi',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
AdminOpsControllerd8ea95465d19058c1124a4a676403642.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerd8ea95465d19058c1124a4a676403642.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
AdminOpsControllerd8ea95465d19058c1124a4a676403642.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
AdminOpsControllerd8ea95465d19058c1124a4a676403642.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
    const AdminOpsControllerd8ea95465d19058c1124a4a676403642Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
        AdminOpsControllerd8ea95465d19058c1124a4a676403642Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/tarif-bagasi'
 */
        AdminOpsControllerd8ea95465d19058c1124a4a676403642Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd8ea95465d19058c1124a4a676403642.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerd8ea95465d19058c1124a4a676403642.form = AdminOpsControllerd8ea95465d19058c1124a4a676403642Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
const AdminOpsControllere334d50fc7952088e181b32d23405957 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllere334d50fc7952088e181b32d23405957.url(options),
    method: 'get',
})

AdminOpsControllere334d50fc7952088e181b32d23405957.definition = {
    methods: ["get","head"],
    url: '/settings/segments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
AdminOpsControllere334d50fc7952088e181b32d23405957.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllere334d50fc7952088e181b32d23405957.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
AdminOpsControllere334d50fc7952088e181b32d23405957.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllere334d50fc7952088e181b32d23405957.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
AdminOpsControllere334d50fc7952088e181b32d23405957.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllere334d50fc7952088e181b32d23405957.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
    const AdminOpsControllere334d50fc7952088e181b32d23405957Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllere334d50fc7952088e181b32d23405957.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
        AdminOpsControllere334d50fc7952088e181b32d23405957Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllere334d50fc7952088e181b32d23405957.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/segments'
 */
        AdminOpsControllere334d50fc7952088e181b32d23405957Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllere334d50fc7952088e181b32d23405957.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllere334d50fc7952088e181b32d23405957.form = AdminOpsControllere334d50fc7952088e181b32d23405957Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
const AdminOpsControlleracd2087366da8180919d3d7849c93362 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleracd2087366da8180919d3d7849c93362.url(options),
    method: 'get',
})

AdminOpsControlleracd2087366da8180919d3d7849c93362.definition = {
    methods: ["get","head"],
    url: '/settings/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
AdminOpsControlleracd2087366da8180919d3d7849c93362.url = (options?: RouteQueryOptions) => {
    return AdminOpsControlleracd2087366da8180919d3d7849c93362.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
AdminOpsControlleracd2087366da8180919d3d7849c93362.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleracd2087366da8180919d3d7849c93362.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
AdminOpsControlleracd2087366da8180919d3d7849c93362.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControlleracd2087366da8180919d3d7849c93362.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
    const AdminOpsControlleracd2087366da8180919d3d7849c93362Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControlleracd2087366da8180919d3d7849c93362.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
        AdminOpsControlleracd2087366da8180919d3d7849c93362Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleracd2087366da8180919d3d7849c93362.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/customers'
 */
        AdminOpsControlleracd2087366da8180919d3d7849c93362Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleracd2087366da8180919d3d7849c93362.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControlleracd2087366da8180919d3d7849c93362.form = AdminOpsControlleracd2087366da8180919d3d7849c93362Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
const AdminOpsController11afde0db631d55773bc7970a2a10380 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController11afde0db631d55773bc7970a2a10380.url(options),
    method: 'get',
})

AdminOpsController11afde0db631d55773bc7970a2a10380.definition = {
    methods: ["get","head"],
    url: '/settings/kategori-armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
AdminOpsController11afde0db631d55773bc7970a2a10380.url = (options?: RouteQueryOptions) => {
    return AdminOpsController11afde0db631d55773bc7970a2a10380.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
AdminOpsController11afde0db631d55773bc7970a2a10380.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController11afde0db631d55773bc7970a2a10380.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
AdminOpsController11afde0db631d55773bc7970a2a10380.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController11afde0db631d55773bc7970a2a10380.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
    const AdminOpsController11afde0db631d55773bc7970a2a10380Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController11afde0db631d55773bc7970a2a10380.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
        AdminOpsController11afde0db631d55773bc7970a2a10380Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController11afde0db631d55773bc7970a2a10380.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada'
 */
        AdminOpsController11afde0db631d55773bc7970a2a10380Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController11afde0db631d55773bc7970a2a10380.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController11afde0db631d55773bc7970a2a10380.form = AdminOpsController11afde0db631d55773bc7970a2a10380Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
const AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, options),
    method: 'get',
})

AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.definition = {
    methods: ["get","head"],
    url: '/settings/kategori-armada/layout/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
    const AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
        AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/kategori-armada/layout/{id}'
 */
        AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7.form = AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
const AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url(options),
    method: 'get',
})

AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.definition = {
    methods: ["get","head"],
    url: '/settings/armada',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
    const AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addebForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
        AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addebForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada'
 */
        AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addebForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb.form = AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addebForm
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
const AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, options),
    method: 'get',
})

AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.definition = {
    methods: ["get","head"],
    url: '/settings/armada/view/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
    const AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
        AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/armada/view/{id}'
 */
        AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0.form = AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
const AdminOpsControllerc67ea54db531f99ab92509c506d38ce6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url(options),
    method: 'get',
})

AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.definition = {
    methods: ["get","head"],
    url: '/settings/pool',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
    const AdminOpsControllerc67ea54db531f99ab92509c506d38ce6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
        AdminOpsControllerc67ea54db531f99ab92509c506d38ce6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/pool'
 */
        AdminOpsControllerc67ea54db531f99ab92509c506d38ce6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerc67ea54db531f99ab92509c506d38ce6.form = AdminOpsControllerc67ea54db531f99ab92509c506d38ce6Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
const AdminOpsControllerd6032201be53d2a88446e06d93683be3 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url(options),
    method: 'get',
})

AdminOpsControllerd6032201be53d2a88446e06d93683be3.definition = {
    methods: ["get","head"],
    url: '/settings/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
AdminOpsControllerd6032201be53d2a88446e06d93683be3.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerd6032201be53d2a88446e06d93683be3.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
AdminOpsControllerd6032201be53d2a88446e06d93683be3.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
AdminOpsControllerd6032201be53d2a88446e06d93683be3.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
    const AdminOpsControllerd6032201be53d2a88446e06d93683be3Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
        AdminOpsControllerd6032201be53d2a88446e06d93683be3Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/users'
 */
        AdminOpsControllerd6032201be53d2a88446e06d93683be3Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerd6032201be53d2a88446e06d93683be3.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerd6032201be53d2a88446e06d93683be3.form = AdminOpsControllerd6032201be53d2a88446e06d93683be3Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
const AdminOpsController64722bec5f26f82352a02f1eb8df9e52 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url(options),
    method: 'get',
})

AdminOpsController64722bec5f26f82352a02f1eb8df9e52.definition = {
    methods: ["get","head"],
    url: '/settings/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url = (options?: RouteQueryOptions) => {
    return AdminOpsController64722bec5f26f82352a02f1eb8df9e52.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
AdminOpsController64722bec5f26f82352a02f1eb8df9e52.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
AdminOpsController64722bec5f26f82352a02f1eb8df9e52.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
    const AdminOpsController64722bec5f26f82352a02f1eb8df9e52Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
        AdminOpsController64722bec5f26f82352a02f1eb8df9e52Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/roles'
 */
        AdminOpsController64722bec5f26f82352a02f1eb8df9e52Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController64722bec5f26f82352a02f1eb8df9e52.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController64722bec5f26f82352a02f1eb8df9e52.form = AdminOpsController64722bec5f26f82352a02f1eb8df9e52Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
const AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url(options),
    method: 'get',
})

AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.definition = {
    methods: ["get","head"],
    url: '/settings/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url = (options?: RouteQueryOptions) => {
    return AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
    const AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
        AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/logs'
 */
        AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8.form = AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8Form
    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
const AdminOpsController188f0ba93346eadbdbf8e618c21963d9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url(options),
    method: 'get',
})

AdminOpsController188f0ba93346eadbdbf8e618c21963d9.definition = {
    methods: ["get","head"],
    url: '/settings/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url = (options?: RouteQueryOptions) => {
    return AdminOpsController188f0ba93346eadbdbf8e618c21963d9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
AdminOpsController188f0ba93346eadbdbf8e618c21963d9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
AdminOpsController188f0ba93346eadbdbf8e618c21963d9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
    const AdminOpsController188f0ba93346eadbdbf8e618c21963d9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
        AdminOpsController188f0ba93346eadbdbf8e618c21963d9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminOpsController::__invoke
 * @see app/Http/Controllers/AdminOpsController.php:32
 * @route '/settings/reports'
 */
        AdminOpsController188f0ba93346eadbdbf8e618c21963d9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: AdminOpsController188f0ba93346eadbdbf8e618c21963d9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    AdminOpsController188f0ba93346eadbdbf8e618c21963d9.form = AdminOpsController188f0ba93346eadbdbf8e618c21963d9Form

/**
* Multiple routes resolve to \App\Http\Controllers\AdminOpsController::AdminOpsController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `AdminOpsController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const AdminOpsController = {
    '/report': AdminOpsController09c49cc81052ab7bc3ccda515af769ef,
    '/reports': AdminOpsController58ce3b21459752ee73930d924bf98aec,
    '/settings/rute-induk': AdminOpsControllerd8fdcdec782d14d5d25c0857f4619dad,
    '/settings/jadwal': AdminOpsControllerccce07e58dc0fe177d1e98eda98a22e6,
    '/settings/driver': AdminOpsController29d56f4279284c9126127f89b74091e6,
    '/settings/tarif-bagasi': AdminOpsControllerd8ea95465d19058c1124a4a676403642,
    '/settings/segments': AdminOpsControllere334d50fc7952088e181b32d23405957,
    '/settings/customers': AdminOpsControlleracd2087366da8180919d3d7849c93362,
    '/settings/kategori-armada': AdminOpsController11afde0db631d55773bc7970a2a10380,
    '/settings/kategori-armada/layout/{id}': AdminOpsController16fdd9ebd7e54b7979b44faf98d8bdb7,
    '/settings/armada': AdminOpsControllerf01dde2d0c77ed6f6a7aaef1110addeb,
    '/settings/armada/view/{id}': AdminOpsControlleref3749058e90b754f05ea3d0adf0cfa0,
    '/settings/pool': AdminOpsControllerc67ea54db531f99ab92509c506d38ce6,
    '/settings/users': AdminOpsControllerd6032201be53d2a88446e06d93683be3,
    '/settings/roles': AdminOpsController64722bec5f26f82352a02f1eb8df9e52,
    '/settings/logs': AdminOpsControllerc4d7a7c0f553568a41b9f810647ae9b8,
    '/settings/reports': AdminOpsController188f0ba93346eadbdbf8e618c21963d9,
}

export default AdminOpsController