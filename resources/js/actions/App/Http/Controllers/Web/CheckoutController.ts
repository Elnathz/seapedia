import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
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
 * @see app/Http/Controllers/Web/CheckoutController.php:23
 * @route '/buyer/checkout'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
 * @route '/buyer/checkout'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
 * @route '/buyer/checkout'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
 * @route '/buyer/checkout'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
 * @route '/buyer/checkout'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\CheckoutController::show
 * @see app/Http/Controllers/Web/CheckoutController.php:23
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
 * @see app/Http/Controllers/Web/CheckoutController.php:59
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
 * @see app/Http/Controllers/Web/CheckoutController.php:59
 * @route '/buyer/checkout'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:59
 * @route '/buyer/checkout'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:59
 * @route '/buyer/checkout'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\CheckoutController::store
 * @see app/Http/Controllers/Web/CheckoutController.php:59
 * @route '/buyer/checkout'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const CheckoutController = { show, store }

export default CheckoutController