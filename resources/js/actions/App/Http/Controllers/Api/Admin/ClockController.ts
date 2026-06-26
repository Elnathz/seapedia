import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\Admin\ClockController::advance
* @see app/Http/Controllers/Api/Admin/ClockController.php:29
* @route '/api/v1/admin/clock/advance'
*/
export const advance = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: advance.url(options),
    method: 'post',
})

advance.definition = {
    methods: ["post"],
    url: '/api/v1/admin/clock/advance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\Admin\ClockController::advance
* @see app/Http/Controllers/Api/Admin/ClockController.php:29
* @route '/api/v1/admin/clock/advance'
*/
advance.url = (options?: RouteQueryOptions) => {
    return advance.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\ClockController::advance
* @see app/Http/Controllers/Api/Admin/ClockController.php:29
* @route '/api/v1/admin/clock/advance'
*/
advance.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: advance.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\Admin\ClockController::advance
* @see app/Http/Controllers/Api/Admin/ClockController.php:29
* @route '/api/v1/admin/clock/advance'
*/
const advanceForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: advance.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\Admin\ClockController::advance
* @see app/Http/Controllers/Api/Admin/ClockController.php:29
* @route '/api/v1/admin/clock/advance'
*/
advanceForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: advance.url(options),
    method: 'post',
})

advance.form = advanceForm

const ClockController = { advance }

export default ClockController