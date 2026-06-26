import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
import clock from './clock'
import promos from './promos'
import vouchers from './vouchers'
/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/api/v1/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\DashboardController::dashboard
* @see app/Http/Controllers/Api/Admin/DashboardController.php:24
* @route '/api/v1/admin/dashboard'
*/
dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

dashboard.form = dashboardForm

const admin = {
    dashboard: Object.assign(dashboard, dashboard),
    clock: Object.assign(clock, clock),
    promos: Object.assign(promos, promos),
    vouchers: Object.assign(vouchers, vouchers),
}

export default admin