import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/seller/store',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::show
* @see app/Http/Controllers/Web/SellerStoreController.php:24
* @route '/seller/store'
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
* @see \App\Http\Controllers\Web\SellerStoreController::store
* @see app/Http/Controllers/Web/SellerStoreController.php:31
* @route '/seller/store'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/seller/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\SellerStoreController::store
* @see app/Http/Controllers/Web/SellerStoreController.php:31
* @route '/seller/store'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\SellerStoreController::store
* @see app/Http/Controllers/Web/SellerStoreController.php:31
* @route '/seller/store'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::store
* @see app/Http/Controllers/Web/SellerStoreController.php:31
* @route '/seller/store'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::store
* @see app/Http/Controllers/Web/SellerStoreController.php:31
* @route '/seller/store'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Web\SellerStoreController::update
* @see app/Http/Controllers/Web/SellerStoreController.php:42
* @route '/seller/store/{store}'
*/
export const update = (args: { store: number | { id: number } } | [store: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/seller/store/{store}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Web\SellerStoreController::update
* @see app/Http/Controllers/Web/SellerStoreController.php:42
* @route '/seller/store/{store}'
*/
update.url = (args: { store: number | { id: number } } | [store: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { store: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { store: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            store: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        store: typeof args.store === 'object'
        ? args.store.id
        : args.store,
    }

    return update.definition.url
            .replace('{store}', parsedArgs.store.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\SellerStoreController::update
* @see app/Http/Controllers/Web/SellerStoreController.php:42
* @route '/seller/store/{store}'
*/
update.put = (args: { store: number | { id: number } } | [store: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::update
* @see app/Http/Controllers/Web/SellerStoreController.php:42
* @route '/seller/store/{store}'
*/
const updateForm = (args: { store: number | { id: number } } | [store: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\SellerStoreController::update
* @see app/Http/Controllers/Web/SellerStoreController.php:42
* @route '/seller/store/{store}'
*/
updateForm.put = (args: { store: number | { id: number } } | [store: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const SellerStoreController = { show, store, update }

export default SellerStoreController