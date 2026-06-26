import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import topup6831ab from './topup'
/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/buyer/wallet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
* @see app/Http/Controllers/Web/BuyerWalletController.php:18
* @route '/buyer/wallet'
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
* @see \App\Http\Controllers\Web\BuyerWalletController::topup
* @see app/Http/Controllers/Web/BuyerWalletController.php:29
* @route '/buyer/wallet/topup'
*/
export const topup = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: topup.url(options),
    method: 'post',
})

topup.definition = {
    methods: ["post"],
    url: '/buyer/wallet/topup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::topup
* @see app/Http/Controllers/Web/BuyerWalletController.php:29
* @route '/buyer/wallet/topup'
*/
topup.url = (options?: RouteQueryOptions) => {
    return topup.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::topup
* @see app/Http/Controllers/Web/BuyerWalletController.php:29
* @route '/buyer/wallet/topup'
*/
topup.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: topup.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::topup
* @see app/Http/Controllers/Web/BuyerWalletController.php:29
* @route '/buyer/wallet/topup'
*/
const topupForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: topup.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::topup
* @see app/Http/Controllers/Web/BuyerWalletController.php:29
* @route '/buyer/wallet/topup'
*/
topupForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: topup.url(options),
    method: 'post',
})

topup.form = topupForm

const wallet = {
    show: Object.assign(show, show),
    topup: Object.assign(topup, topup6831ab),
}

export default wallet