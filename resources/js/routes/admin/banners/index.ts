import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
export const searchCategories = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchCategories.url(options),
    method: 'get',
})

searchCategories.definition = {
    methods: ["get","head"],
    url: '/admin/banners/search-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
searchCategories.url = (options?: RouteQueryOptions) => {
    return searchCategories.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
searchCategories.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchCategories.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
searchCategories.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchCategories.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
    const searchCategoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: searchCategories.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
        searchCategoriesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchCategories.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchCategories
 * @see app/Http/Controllers/Web/Admin/BannerController.php:20
 * @route '/admin/banners/search-categories'
 */
        searchCategoriesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchCategories.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    searchCategories.form = searchCategoriesForm
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
export const searchProducts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchProducts.url(options),
    method: 'get',
})

searchProducts.definition = {
    methods: ["get","head"],
    url: '/admin/banners/search-products',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
searchProducts.url = (options?: RouteQueryOptions) => {
    return searchProducts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
searchProducts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchProducts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
searchProducts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchProducts.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
    const searchProductsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: searchProducts.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
        searchProductsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchProducts.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchProducts
 * @see app/Http/Controllers/Web/Admin/BannerController.php:38
 * @route '/admin/banners/search-products'
 */
        searchProductsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchProducts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    searchProducts.form = searchProductsForm
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
export const searchStores = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchStores.url(options),
    method: 'get',
})

searchStores.definition = {
    methods: ["get","head"],
    url: '/admin/banners/search-stores',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
searchStores.url = (options?: RouteQueryOptions) => {
    return searchStores.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
searchStores.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchStores.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
searchStores.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchStores.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
    const searchStoresForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: searchStores.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
        searchStoresForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchStores.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::searchStores
 * @see app/Http/Controllers/Web/Admin/BannerController.php:56
 * @route '/admin/banners/search-stores'
 */
        searchStoresForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchStores.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    searchStores.form = searchStoresForm
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/banners',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::index
 * @see app/Http/Controllers/Web/Admin/BannerController.php:74
 * @route '/admin/banners'
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
* @see \App\Http\Controllers\Web\Admin\BannerController::store
 * @see app/Http/Controllers/Web/Admin/BannerController.php:79
 * @route '/admin/banners'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/banners',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::store
 * @see app/Http/Controllers/Web/Admin/BannerController.php:79
 * @route '/admin/banners'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::store
 * @see app/Http/Controllers/Web/Admin/BannerController.php:79
 * @route '/admin/banners'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::store
 * @see app/Http/Controllers/Web/Admin/BannerController.php:79
 * @route '/admin/banners'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::store
 * @see app/Http/Controllers/Web/Admin/BannerController.php:79
 * @route '/admin/banners'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Web\Admin\BannerController::update
 * @see app/Http/Controllers/Web/Admin/BannerController.php:87
 * @route '/admin/banners/{banner}'
 */
export const update = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/banners/{banner}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::update
 * @see app/Http/Controllers/Web/Admin/BannerController.php:87
 * @route '/admin/banners/{banner}'
 */
update.url = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { banner: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: typeof args.banner === 'object'
                ? args.banner.id
                : args.banner,
                }

    return update.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::update
 * @see app/Http/Controllers/Web/Admin/BannerController.php:87
 * @route '/admin/banners/{banner}'
 */
update.put = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::update
 * @see app/Http/Controllers/Web/Admin/BannerController.php:87
 * @route '/admin/banners/{banner}'
 */
    const updateForm = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::update
 * @see app/Http/Controllers/Web/Admin/BannerController.php:87
 * @route '/admin/banners/{banner}'
 */
        updateForm.put = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Web\Admin\BannerController::destroy
 * @see app/Http/Controllers/Web/Admin/BannerController.php:95
 * @route '/admin/banners/{banner}'
 */
export const destroy = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/banners/{banner}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::destroy
 * @see app/Http/Controllers/Web/Admin/BannerController.php:95
 * @route '/admin/banners/{banner}'
 */
destroy.url = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { banner: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: typeof args.banner === 'object'
                ? args.banner.id
                : args.banner,
                }

    return destroy.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\BannerController::destroy
 * @see app/Http/Controllers/Web/Admin/BannerController.php:95
 * @route '/admin/banners/{banner}'
 */
destroy.delete = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Web\Admin\BannerController::destroy
 * @see app/Http/Controllers/Web/Admin/BannerController.php:95
 * @route '/admin/banners/{banner}'
 */
    const destroyForm = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\BannerController::destroy
 * @see app/Http/Controllers/Web/Admin/BannerController.php:95
 * @route '/admin/banners/{banner}'
 */
        destroyForm.delete = (args: { banner: number | { id: number } } | [banner: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const banners = {
    searchCategories: Object.assign(searchCategories, searchCategories),
searchProducts: Object.assign(searchProducts, searchProducts),
searchStores: Object.assign(searchStores, searchStores),
index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default banners