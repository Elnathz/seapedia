import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/buyer/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\BuyerCartController::index
 * @see app/Http/Controllers/Web/BuyerCartController.php:20
 * @route '/buyer/cart'
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
* @see \App\Http\Controllers\Web\BuyerCartController::store
 * @see app/Http/Controllers/Web/BuyerCartController.php:27
 * @route '/buyer/cart'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/buyer/cart',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\BuyerCartController::store
 * @see app/Http/Controllers/Web/BuyerCartController.php:27
 * @route '/buyer/cart'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerCartController::store
 * @see app/Http/Controllers/Web/BuyerCartController.php:27
 * @route '/buyer/cart'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\BuyerCartController::store
 * @see app/Http/Controllers/Web/BuyerCartController.php:27
 * @route '/buyer/cart'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\BuyerCartController::store
 * @see app/Http/Controllers/Web/BuyerCartController.php:27
 * @route '/buyer/cart'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Web\BuyerCartController::update
 * @see app/Http/Controllers/Web/BuyerCartController.php:57
 * @route '/buyer/cart/{item}'
 */
export const update = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/buyer/cart/{item}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Web\BuyerCartController::update
 * @see app/Http/Controllers/Web/BuyerCartController.php:57
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::update
 * @see app/Http/Controllers/Web/BuyerCartController.php:57
 * @route '/buyer/cart/{item}'
 */
update.put = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Web\BuyerCartController::update
 * @see app/Http/Controllers/Web/BuyerCartController.php:57
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::update
 * @see app/Http/Controllers/Web/BuyerCartController.php:57
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::destroy
 * @see app/Http/Controllers/Web/BuyerCartController.php:68
 * @route '/buyer/cart/{item}'
 */
export const destroy = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/buyer/cart/{item}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Web\BuyerCartController::destroy
 * @see app/Http/Controllers/Web/BuyerCartController.php:68
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::destroy
 * @see app/Http/Controllers/Web/BuyerCartController.php:68
 * @route '/buyer/cart/{item}'
 */
destroy.delete = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Web\BuyerCartController::destroy
 * @see app/Http/Controllers/Web/BuyerCartController.php:68
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::destroy
 * @see app/Http/Controllers/Web/BuyerCartController.php:68
 * @route '/buyer/cart/{item}'
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
* @see \App\Http\Controllers\Web\BuyerCartController::clear
 * @see app/Http/Controllers/Web/BuyerCartController.php:79
 * @route '/buyer/cart'
 */
export const clear = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

clear.definition = {
    methods: ["delete"],
    url: '/buyer/cart',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Web\BuyerCartController::clear
 * @see app/Http/Controllers/Web/BuyerCartController.php:79
 * @route '/buyer/cart'
 */
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BuyerCartController::clear
 * @see app/Http/Controllers/Web/BuyerCartController.php:79
 * @route '/buyer/cart'
 */
clear.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Web\BuyerCartController::clear
 * @see app/Http/Controllers/Web/BuyerCartController.php:79
 * @route '/buyer/cart'
 */
    const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clear.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\BuyerCartController::clear
 * @see app/Http/Controllers/Web/BuyerCartController.php:79
 * @route '/buyer/cart'
 */
        clearForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clear.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    clear.form = clearForm
const BuyerCartController = { index, store, update, destroy, clear }

export default BuyerCartController