import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\RoleController::store
 * @see app/Http/Controllers/Api/RoleController.php:36
 * @route '/api/v1/role/select'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/role/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\RoleController::store
 * @see app/Http/Controllers/Api/RoleController.php:36
 * @route '/api/v1/role/select'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\RoleController::store
 * @see app/Http/Controllers/Api/RoleController.php:36
 * @route '/api/v1/role/select'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\RoleController::store
 * @see app/Http/Controllers/Api/RoleController.php:36
 * @route '/api/v1/role/select'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\RoleController::store
 * @see app/Http/Controllers/Api/RoleController.php:36
 * @route '/api/v1/role/select'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const RoleController = { store }

export default RoleController