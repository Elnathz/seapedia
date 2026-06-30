import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/catalog',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\CatalogController::index
 * @see app/Http/Controllers/Web/CatalogController.php:27
 * @route '/catalog'
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
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
export const show = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/catalog/{product}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
show.url = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { product: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    product: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        product: args.product,
                }

    return show.definition.url
            .replace('{product}', parsedArgs.product.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
show.get = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
show.head = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
    const showForm = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
        showForm.get = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\CatalogController::show
 * @see app/Http/Controllers/Web/CatalogController.php:86
 * @route '/catalog/{product}'
 */
        showForm.head = (args: { product: string | number } | [product: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const catalog = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
}

export default catalog