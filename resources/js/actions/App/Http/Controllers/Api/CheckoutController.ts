import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CheckoutController::preview
* @see app/Http/Controllers/Api/CheckoutController.php:38
* @route '/api/v1/buyer/checkout/preview'
*/
export const preview = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview.url(options),
    method: 'post',
})

preview.definition = {
    methods: ["post"],
    url: '/api/v1/buyer/checkout/preview',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CheckoutController::preview
* @see app/Http/Controllers/Api/CheckoutController.php:38
* @route '/api/v1/buyer/checkout/preview'
*/
preview.url = (options?: RouteQueryOptions) => {
    return preview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CheckoutController::preview
* @see app/Http/Controllers/Api/CheckoutController.php:38
* @route '/api/v1/buyer/checkout/preview'
*/
preview.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: preview.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\CheckoutController::preview
* @see app/Http/Controllers/Api/CheckoutController.php:38
* @route '/api/v1/buyer/checkout/preview'
*/
const previewForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: preview.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\CheckoutController::preview
* @see app/Http/Controllers/Api/CheckoutController.php:38
* @route '/api/v1/buyer/checkout/preview'
*/
previewForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: preview.url(options),
    method: 'post',
})

preview.form = previewForm

/**
* @see \App\Http\Controllers\Api\CheckoutController::store
* @see app/Http/Controllers/Api/CheckoutController.php:73
* @route '/api/v1/buyer/checkout'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/buyer/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\CheckoutController::store
* @see app/Http/Controllers/Api/CheckoutController.php:73
* @route '/api/v1/buyer/checkout'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CheckoutController::store
* @see app/Http/Controllers/Api/CheckoutController.php:73
* @route '/api/v1/buyer/checkout'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\CheckoutController::store
* @see app/Http/Controllers/Api/CheckoutController.php:73
* @route '/api/v1/buyer/checkout'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\CheckoutController::store
* @see app/Http/Controllers/Api/CheckoutController.php:73
* @route '/api/v1/buyer/checkout'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const CheckoutController = { preview, store }

export default CheckoutController