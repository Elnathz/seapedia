import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/buyer/wallet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::show
* @see app/Http/Controllers/Api/BuyerWalletController.php:26
* @route '/api/v1/buyer/wallet'
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
* @see \App\Http\Controllers\Api\BuyerWalletController::store
* @see app/Http/Controllers/Api/BuyerWalletController.php:55
* @route '/api/v1/buyer/wallet/topup'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/buyer/wallet/topup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::store
* @see app/Http/Controllers/Api/BuyerWalletController.php:55
* @route '/api/v1/buyer/wallet/topup'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::store
* @see app/Http/Controllers/Api/BuyerWalletController.php:55
* @route '/api/v1/buyer/wallet/topup'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::store
* @see app/Http/Controllers/Api/BuyerWalletController.php:55
* @route '/api/v1/buyer/wallet/topup'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::store
* @see app/Http/Controllers/Api/BuyerWalletController.php:55
* @route '/api/v1/buyer/wallet/topup'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
export const topup = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: topup.url(args, options),
    method: 'get',
})

topup.definition = {
    methods: ["get","head"],
    url: '/api/v1/buyer/wallet/topup/{topup}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
topup.url = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { topup: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { topup: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            topup: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        topup: typeof args.topup === 'object'
        ? args.topup.id
        : args.topup,
    }

    return topup.definition.url
            .replace('{topup}', parsedArgs.topup.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
topup.get = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: topup.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
topup.head = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: topup.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
const topupForm = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: topup.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
topupForm.get = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: topup.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\BuyerWalletController::topup
* @see app/Http/Controllers/Api/BuyerWalletController.php:72
* @route '/api/v1/buyer/wallet/topup/{topup}'
*/
topupForm.head = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: topup.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

topup.form = topupForm

const BuyerWalletController = { show, store, topup }

export default BuyerWalletController