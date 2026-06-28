import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/stores',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\AdminStoreController::index
* @see app/Http/Controllers/Web/Admin/AdminStoreController.php:13
* @route '/admin/stores'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const AdminStoreController = { index }

export default AdminStoreController