import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\Seller\OrderController::process
 * @see app/Http/Controllers/Api/Seller/OrderController.php:27
 * @route '/api/v1/seller/orders/{order}/process'
 */
export const process = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/api/v1/seller/orders/{order}/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\Seller\OrderController::process
 * @see app/Http/Controllers/Api/Seller/OrderController.php:27
 * @route '/api/v1/seller/orders/{order}/process'
 */
process.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return process.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Seller\OrderController::process
 * @see app/Http/Controllers/Api/Seller/OrderController.php:27
 * @route '/api/v1/seller/orders/{order}/process'
 */
process.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\Seller\OrderController::process
 * @see app/Http/Controllers/Api/Seller/OrderController.php:27
 * @route '/api/v1/seller/orders/{order}/process'
 */
    const processForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: process.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\Seller\OrderController::process
 * @see app/Http/Controllers/Api/Seller/OrderController.php:27
 * @route '/api/v1/seller/orders/{order}/process'
 */
        processForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(args, options),
            method: 'post',
        })
    
    process.form = processForm
const OrderController = { process }

export default OrderController