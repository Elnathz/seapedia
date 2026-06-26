import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/buyer/addresses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::index
* @see app/Http/Controllers/Web/BuyerAddressController.php:19
* @route '/buyer/addresses'
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
* @see \App\Http\Controllers\Web\BuyerAddressController::store
* @see app/Http/Controllers/Web/BuyerAddressController.php:30
* @route '/buyer/addresses'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/buyer/addresses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::store
* @see app/Http/Controllers/Web/BuyerAddressController.php:30
* @route '/buyer/addresses'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::store
* @see app/Http/Controllers/Web/BuyerAddressController.php:30
* @route '/buyer/addresses'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::store
* @see app/Http/Controllers/Web/BuyerAddressController.php:30
* @route '/buyer/addresses'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::store
* @see app/Http/Controllers/Web/BuyerAddressController.php:30
* @route '/buyer/addresses'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::update
* @see app/Http/Controllers/Web/BuyerAddressController.php:39
* @route '/buyer/addresses/{address}'
*/
export const update = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/buyer/addresses/{address}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::update
* @see app/Http/Controllers/Web/BuyerAddressController.php:39
* @route '/buyer/addresses/{address}'
*/
update.url = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { address: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { address: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            address: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        address: typeof args.address === 'object'
        ? args.address.id
        : args.address,
    }

    return update.definition.url
            .replace('{address}', parsedArgs.address.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::update
* @see app/Http/Controllers/Web/BuyerAddressController.php:39
* @route '/buyer/addresses/{address}'
*/
update.put = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::update
* @see app/Http/Controllers/Web/BuyerAddressController.php:39
* @route '/buyer/addresses/{address}'
*/
const updateForm = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::update
* @see app/Http/Controllers/Web/BuyerAddressController.php:39
* @route '/buyer/addresses/{address}'
*/
updateForm.put = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::setDefault
* @see app/Http/Controllers/Web/BuyerAddressController.php:50
* @route '/buyer/addresses/{address}/default'
*/
export const setDefault = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: setDefault.url(args, options),
    method: 'patch',
})

setDefault.definition = {
    methods: ["patch"],
    url: '/buyer/addresses/{address}/default',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::setDefault
* @see app/Http/Controllers/Web/BuyerAddressController.php:50
* @route '/buyer/addresses/{address}/default'
*/
setDefault.url = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { address: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { address: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            address: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        address: typeof args.address === 'object'
        ? args.address.id
        : args.address,
    }

    return setDefault.definition.url
            .replace('{address}', parsedArgs.address.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::setDefault
* @see app/Http/Controllers/Web/BuyerAddressController.php:50
* @route '/buyer/addresses/{address}/default'
*/
setDefault.patch = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: setDefault.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::setDefault
* @see app/Http/Controllers/Web/BuyerAddressController.php:50
* @route '/buyer/addresses/{address}/default'
*/
const setDefaultForm = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: setDefault.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::setDefault
* @see app/Http/Controllers/Web/BuyerAddressController.php:50
* @route '/buyer/addresses/{address}/default'
*/
setDefaultForm.patch = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: setDefault.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

setDefault.form = setDefaultForm

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::destroy
* @see app/Http/Controllers/Web/BuyerAddressController.php:61
* @route '/buyer/addresses/{address}'
*/
export const destroy = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/buyer/addresses/{address}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::destroy
* @see app/Http/Controllers/Web/BuyerAddressController.php:61
* @route '/buyer/addresses/{address}'
*/
destroy.url = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { address: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { address: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            address: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        address: typeof args.address === 'object'
        ? args.address.id
        : args.address,
    }

    return destroy.definition.url
            .replace('{address}', parsedArgs.address.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::destroy
* @see app/Http/Controllers/Web/BuyerAddressController.php:61
* @route '/buyer/addresses/{address}'
*/
destroy.delete = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::destroy
* @see app/Http/Controllers/Web/BuyerAddressController.php:61
* @route '/buyer/addresses/{address}'
*/
const destroyForm = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\BuyerAddressController::destroy
* @see app/Http/Controllers/Web/BuyerAddressController.php:61
* @route '/buyer/addresses/{address}'
*/
destroyForm.delete = (args: { address: number | { id: number } } | [address: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const addresses = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    setDefault: Object.assign(setDefault, setDefault),
    destroy: Object.assign(destroy, destroy),
}

export default addresses