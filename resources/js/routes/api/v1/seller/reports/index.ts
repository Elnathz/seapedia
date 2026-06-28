import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/seller/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\SellerReportController::index
 * @see app/Http/Controllers/Api/SellerReportController.php:25
 * @route '/api/v1/seller/reports'
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
const reports = {
    index: Object.assign(index, index),
}

export default reports