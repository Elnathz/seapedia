import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
export const show = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/stores/{store}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
show.url = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { store: args }
    }

    if (Array.isArray(args)) {
        args = {
            store: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        store: args.store,
    }

    return show.definition.url
            .replace('{store}', parsedArgs.store.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
show.get = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
show.head = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
const showForm = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
showForm.get = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\StoreController::show
* @see app/Http/Controllers/Web/StoreController.php:17
* @route '/stores/{store}'
*/
showForm.head = (args: { store: string | number } | [store: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const StoreController = { show }

export default StoreController