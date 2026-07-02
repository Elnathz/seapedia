import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/seller/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\SellerOrderController::index
 * @see app/Http/Controllers/Web/SellerOrderController.php:21
 * @route '/seller/orders'
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
/**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
export const show = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/seller/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
show.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
show.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
show.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
    const showForm = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
        showForm.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\SellerOrderController::show
 * @see app/Http/Controllers/Web/SellerOrderController.php:38
 * @route '/seller/orders/{order}'
 */
        showForm.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Web\SellerOrderController::process
 * @see app/Http/Controllers/Web/SellerOrderController.php:55
 * @route '/seller/orders/{order}/process'
 */
export const process = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/seller/orders/{order}/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\SellerOrderController::process
 * @see app/Http/Controllers/Web/SellerOrderController.php:55
 * @route '/seller/orders/{order}/process'
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
* @see \App\Http\Controllers\Web\SellerOrderController::process
 * @see app/Http/Controllers/Web/SellerOrderController.php:55
 * @route '/seller/orders/{order}/process'
 */
process.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\SellerOrderController::process
 * @see app/Http/Controllers/Web/SellerOrderController.php:55
 * @route '/seller/orders/{order}/process'
 */
    const processForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: process.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\SellerOrderController::process
 * @see app/Http/Controllers/Web/SellerOrderController.php:55
 * @route '/seller/orders/{order}/process'
 */
        processForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(args, options),
            method: 'post',
        })
    
    process.form = processForm
const orders = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
process: Object.assign(process, process),
}

export default orders