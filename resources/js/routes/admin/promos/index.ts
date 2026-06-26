import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/promos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::index
* @see app/Http/Controllers/Web/Admin/PromoController.php:18
* @route '/admin/promos'
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
* @see \App\Http\Controllers\Web\Admin\PromoController::store
* @see app/Http/Controllers/Web/Admin/PromoController.php:34
* @route '/admin/promos'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/promos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::store
* @see app/Http/Controllers/Web/Admin/PromoController.php:34
* @route '/admin/promos'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::store
* @see app/Http/Controllers/Web/Admin/PromoController.php:34
* @route '/admin/promos'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::store
* @see app/Http/Controllers/Web/Admin/PromoController.php:34
* @route '/admin/promos'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::store
* @see app/Http/Controllers/Web/Admin/PromoController.php:34
* @route '/admin/promos'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
export const show = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/promos/{promo}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
show.url = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promo: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { promo: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            promo: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        promo: typeof args.promo === 'object'
        ? args.promo.id
        : args.promo,
    }

    return show.definition.url
            .replace('{promo}', parsedArgs.promo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
show.get = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
show.head = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
const showForm = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
showForm.get = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::show
* @see app/Http/Controllers/Web/Admin/PromoController.php:29
* @route '/admin/promos/{promo}'
*/
showForm.head = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::toggleActive
* @see app/Http/Controllers/Web/Admin/PromoController.php:46
* @route '/admin/promos/{promo}/toggle-active'
*/
export const toggleActive = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive.url(args, options),
    method: 'patch',
})

toggleActive.definition = {
    methods: ["patch"],
    url: '/admin/promos/{promo}/toggle-active',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::toggleActive
* @see app/Http/Controllers/Web/Admin/PromoController.php:46
* @route '/admin/promos/{promo}/toggle-active'
*/
toggleActive.url = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promo: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { promo: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            promo: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        promo: typeof args.promo === 'object'
        ? args.promo.id
        : args.promo,
    }

    return toggleActive.definition.url
            .replace('{promo}', parsedArgs.promo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::toggleActive
* @see app/Http/Controllers/Web/Admin/PromoController.php:46
* @route '/admin/promos/{promo}/toggle-active'
*/
toggleActive.patch = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::toggleActive
* @see app/Http/Controllers/Web/Admin/PromoController.php:46
* @route '/admin/promos/{promo}/toggle-active'
*/
const toggleActiveForm = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleActive.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Web\Admin\PromoController::toggleActive
* @see app/Http/Controllers/Web/Admin/PromoController.php:46
* @route '/admin/promos/{promo}/toggle-active'
*/
toggleActiveForm.patch = (args: { promo: number | { id: number } } | [promo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleActive.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

toggleActive.form = toggleActiveForm

const promos = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    toggleActive: Object.assign(toggleActive, toggleActive),
}

export default promos