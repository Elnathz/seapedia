import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\RoleController::select
* @see app/Http/Controllers/Api/RoleController.php:36
* @route '/api/v1/role/select'
*/
export const select = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

select.definition = {
    methods: ["post"],
    url: '/api/v1/role/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\RoleController::select
* @see app/Http/Controllers/Api/RoleController.php:36
* @route '/api/v1/role/select'
*/
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\RoleController::select
* @see app/Http/Controllers/Api/RoleController.php:36
* @route '/api/v1/role/select'
*/
select.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\RoleController::select
* @see app/Http/Controllers/Api/RoleController.php:36
* @route '/api/v1/role/select'
*/
const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: select.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\RoleController::select
* @see app/Http/Controllers/Api/RoleController.php:36
* @route '/api/v1/role/select'
*/
selectForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: select.url(options),
    method: 'post',
})

select.form = selectForm

const role = {
    select: Object.assign(select, select),
}

export default role