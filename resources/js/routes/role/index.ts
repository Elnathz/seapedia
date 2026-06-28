import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
export const select = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})

select.definition = {
    methods: ["get","head"],
    url: '/role/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
select.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
select.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: select.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: select.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
selectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: select.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\RoleController::select
* @see app/Http/Controllers/Web/RoleController.php:22
* @route '/role/select'
*/
selectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: select.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

select.form = selectForm

/**
* @see \App\Http\Controllers\Web\RoleController::store
* @see app/Http/Controllers/Web/RoleController.php:32
* @route '/role/select'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/role/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\RoleController::store
* @see app/Http/Controllers/Web/RoleController.php:32
* @route '/role/select'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::store
* @see app/Http/Controllers/Web/RoleController.php:32
* @route '/role/select'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\RoleController::store
* @see app/Http/Controllers/Web/RoleController.php:32
* @route '/role/select'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\RoleController::store
* @see app/Http/Controllers/Web/RoleController.php:32
* @route '/role/select'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const role = {
    select: Object.assign(select, select),
    store: Object.assign(store, store),
}

export default role