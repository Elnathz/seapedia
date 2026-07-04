import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/buyer/checkout',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:27
 * @route '/buyer/checkout'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:65
 * @route '/buyer/checkout'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/buyer/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:65
 * @route '/buyer/checkout'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:65
 * @route '/buyer/checkout'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:65
 * @route '/buyer/checkout'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:65
 * @route '/buyer/checkout'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
export const success = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/buyer/checkout/success/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
success.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return success.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
success.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
success.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
    const successForm = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: success.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
        successForm.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\CheckoutController::success
 * @see app/Http/Controllers/Web/CheckoutController.php:90
 * @route '/buyer/checkout/success/{order}'
 */
        successForm.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    success.form = successForm
const CheckoutController = { show, store, success }

export default CheckoutController