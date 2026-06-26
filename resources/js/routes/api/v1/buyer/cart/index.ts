import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
import items from './items'
/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/buyer/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::show
* @see app/Http/Controllers/Api/BuyerCartController.php:26
* @route '/api/v1/buyer/cart'
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
* @see \App\Http\Controllers\Api\BuyerCartController::clear
* @see app/Http/Controllers/Api/BuyerCartController.php:107
* @route '/api/v1/buyer/cart/clear'
*/
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/api/v1/buyer/cart/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BuyerCartController::clear
* @see app/Http/Controllers/Api/BuyerCartController.php:107
* @route '/api/v1/buyer/cart/clear'
*/
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerCartController::clear
* @see app/Http/Controllers/Api/BuyerCartController.php:107
* @route '/api/v1/buyer/cart/clear'
*/
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::clear
* @see app/Http/Controllers/Api/BuyerCartController.php:107
* @route '/api/v1/buyer/cart/clear'
*/
const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\BuyerCartController::clear
* @see app/Http/Controllers/Api/BuyerCartController.php:107
* @route '/api/v1/buyer/cart/clear'
*/
clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

clear.form = clearForm

const cart = {
    show: Object.assign(show, show),
    items: Object.assign(items, items),
    clear: Object.assign(clear, clear),
}

export default cart