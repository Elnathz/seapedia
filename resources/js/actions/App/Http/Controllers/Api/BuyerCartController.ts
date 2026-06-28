import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
* @see \App\Http\Controllers\Api\BuyerCartController::store
 * @see app/Http/Controllers/Api/BuyerCartController.php:52
 * @route '/api/v1/buyer/cart/items'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/buyer/cart/items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BuyerCartController::store
 * @see app/Http/Controllers/Api/BuyerCartController.php:52
 * @route '/api/v1/buyer/cart/items'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerCartController::store
 * @see app/Http/Controllers/Api/BuyerCartController.php:52
 * @route '/api/v1/buyer/cart/items'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BuyerCartController::store
 * @see app/Http/Controllers/Api/BuyerCartController.php:52
 * @route '/api/v1/buyer/cart/items'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BuyerCartController::store
 * @see app/Http/Controllers/Api/BuyerCartController.php:52
 * @route '/api/v1/buyer/cart/items'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\BuyerCartController::update
 * @see app/Http/Controllers/Api/BuyerCartController.php:77
 * @route '/api/v1/buyer/cart/items/{item}'
 */
export const update = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/v1/buyer/cart/items/{item}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\BuyerCartController::update
 * @see app/Http/Controllers/Api/BuyerCartController.php:77
 * @route '/api/v1/buyer/cart/items/{item}'
 */
update.url = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { item: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    item: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        item: typeof args.item === 'object'
                ? args.item.id
                : args.item,
                }

    return update.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerCartController::update
 * @see app/Http/Controllers/Api/BuyerCartController.php:77
 * @route '/api/v1/buyer/cart/items/{item}'
 */
update.put = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\BuyerCartController::update
 * @see app/Http/Controllers/Api/BuyerCartController.php:77
 * @route '/api/v1/buyer/cart/items/{item}'
 */
    const updateForm = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BuyerCartController::update
 * @see app/Http/Controllers/Api/BuyerCartController.php:77
 * @route '/api/v1/buyer/cart/items/{item}'
 */
        updateForm.put = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Api\BuyerCartController::destroy
 * @see app/Http/Controllers/Api/BuyerCartController.php:91
 * @route '/api/v1/buyer/cart/items/{item}'
 */
export const destroy = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/buyer/cart/items/{item}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\BuyerCartController::destroy
 * @see app/Http/Controllers/Api/BuyerCartController.php:91
 * @route '/api/v1/buyer/cart/items/{item}'
 */
destroy.url = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { item: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    item: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        item: typeof args.item === 'object'
                ? args.item.id
                : args.item,
                }

    return destroy.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BuyerCartController::destroy
 * @see app/Http/Controllers/Api/BuyerCartController.php:91
 * @route '/api/v1/buyer/cart/items/{item}'
 */
destroy.delete = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\BuyerCartController::destroy
 * @see app/Http/Controllers/Api/BuyerCartController.php:91
 * @route '/api/v1/buyer/cart/items/{item}'
 */
    const destroyForm = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BuyerCartController::destroy
 * @see app/Http/Controllers/Api/BuyerCartController.php:91
 * @route '/api/v1/buyer/cart/items/{item}'
 */
        destroyForm.delete = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
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
const BuyerCartController = { show, store, update, destroy, clear }

export default BuyerCartController