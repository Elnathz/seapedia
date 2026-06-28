import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/reviews',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\AppReviewController::index
 * @see app/Http/Controllers/Web/AppReviewController.php:19
 * @route '/reviews'
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
* @see \App\Http\Controllers\Web\AppReviewController::store
 * @see app/Http/Controllers/Web/AppReviewController.php:29
 * @route '/reviews'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/reviews',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\AppReviewController::store
 * @see app/Http/Controllers/Web/AppReviewController.php:29
 * @route '/reviews'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\AppReviewController::store
 * @see app/Http/Controllers/Web/AppReviewController.php:29
 * @route '/reviews'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\AppReviewController::store
 * @see app/Http/Controllers/Web/AppReviewController.php:29
 * @route '/reviews'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\AppReviewController::store
 * @see app/Http/Controllers/Web/AppReviewController.php:29
 * @route '/reviews'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const reviews = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
}

export default reviews