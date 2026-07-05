import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/overdue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::index
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:20
 * @route '/admin/overdue'
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
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::sweep
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:47
 * @route '/admin/overdue/sweep'
 */
export const sweep = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sweep.url(options),
    method: 'post',
})

sweep.definition = {
    methods: ["post"],
    url: '/admin/overdue/sweep',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::sweep
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:47
 * @route '/admin/overdue/sweep'
 */
sweep.url = (options?: RouteQueryOptions) => {
    return sweep.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::sweep
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:47
 * @route '/admin/overdue/sweep'
 */
sweep.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sweep.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::sweep
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:47
 * @route '/admin/overdue/sweep'
 */
    const sweepForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sweep.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\AdminOverdueController::sweep
 * @see app/Http/Controllers/Web/Admin/AdminOverdueController.php:47
 * @route '/admin/overdue/sweep'
 */
        sweepForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sweep.url(options),
            method: 'post',
        })
    
    sweep.form = sweepForm
const overdue = {
    index: Object.assign(index, index),
sweep: Object.assign(sweep, sweep),
}

export default overdue