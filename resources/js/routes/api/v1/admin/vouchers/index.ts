import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/admin/vouchers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::index
* @see app/Http/Controllers/Api/Admin/VoucherController.php:26
* @route '/api/v1/admin/vouchers'
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
* @see \App\Http\Controllers\Api\Admin\VoucherController::store
* @see app/Http/Controllers/Api/Admin/VoucherController.php:41
* @route '/api/v1/admin/vouchers'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/admin/vouchers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::store
* @see app/Http/Controllers/Api/Admin/VoucherController.php:41
* @route '/api/v1/admin/vouchers'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::store
* @see app/Http/Controllers/Api/Admin/VoucherController.php:41
* @route '/api/v1/admin/vouchers'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::store
* @see app/Http/Controllers/Api/Admin/VoucherController.php:41
* @route '/api/v1/admin/vouchers'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::store
* @see app/Http/Controllers/Api/Admin/VoucherController.php:41
* @route '/api/v1/admin/vouchers'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
export const show = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/admin/vouchers/{voucher}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
show.url = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { voucher: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { voucher: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            voucher: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        voucher: typeof args.voucher === 'object'
        ? args.voucher.id
        : args.voucher,
    }

    return show.definition.url
            .replace('{voucher}', parsedArgs.voucher.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
show.get = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
show.head = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
const showForm = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
showForm.get = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\Admin\VoucherController::show
* @see app/Http/Controllers/Api/Admin/VoucherController.php:56
* @route '/api/v1/admin/vouchers/{voucher}'
*/
showForm.head = (args: { voucher: number | { id: number } } | [voucher: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const vouchers = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
}

export default vouchers