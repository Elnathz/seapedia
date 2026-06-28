import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
export const show = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/buyer/wallet/topup/{topup}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
show.url = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{topup}', parsedArgs.topup.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
show.get = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
show.head = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
    const showForm = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
        showForm.get = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\BuyerWalletController::show
 * @see app/Http/Controllers/Web/BuyerWalletController.php:40
 * @route '/buyer/wallet/topup/{topup}'
 */
        showForm.head = (args: { topup: number | { id: number } } | [topup: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const topup = {
    show: Object.assign(show, show),
}

export default topup