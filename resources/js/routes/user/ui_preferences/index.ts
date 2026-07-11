import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\UserPreferenceController::update
 * @see app/Http/Controllers/UserPreferenceController.php:11
 * @route '/api/user/ui-preferences'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/api/user/ui-preferences',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\UserPreferenceController::update
 * @see app/Http/Controllers/UserPreferenceController.php:11
 * @route '/api/user/ui-preferences'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserPreferenceController::update
 * @see app/Http/Controllers/UserPreferenceController.php:11
 * @route '/api/user/ui-preferences'
 */
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\UserPreferenceController::update
 * @see app/Http/Controllers/UserPreferenceController.php:11
 * @route '/api/user/ui-preferences'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UserPreferenceController::update
 * @see app/Http/Controllers/UserPreferenceController.php:11
 * @route '/api/user/ui-preferences'
 */
        updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const ui_preferences = {
    update: Object.assign(update, update),
}

export default ui_preferences