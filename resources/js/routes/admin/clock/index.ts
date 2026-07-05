import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\Admin\ClockController::advance
 * @see app/Http/Controllers/Web/Admin/ClockController.php:24
 * @route '/admin/clock/advance'
 */
export const advance = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: advance.url(options),
    method: 'post',
})

advance.definition = {
    methods: ["post"],
    url: '/admin/clock/advance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\Admin\ClockController::advance
 * @see app/Http/Controllers/Web/Admin/ClockController.php:24
 * @route '/admin/clock/advance'
 */
advance.url = (options?: RouteQueryOptions) => {
    return advance.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\ClockController::advance
 * @see app/Http/Controllers/Web/Admin/ClockController.php:24
 * @route '/admin/clock/advance'
 */
advance.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: advance.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\Admin\ClockController::advance
 * @see app/Http/Controllers/Web/Admin/ClockController.php:24
 * @route '/admin/clock/advance'
 */
    const advanceForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: advance.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\ClockController::advance
 * @see app/Http/Controllers/Web/Admin/ClockController.php:24
 * @route '/admin/clock/advance'
 */
        advanceForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: advance.url(options),
            method: 'post',
        })
    
    advance.form = advanceForm
/**
* @see \App\Http\Controllers\Web\Admin\ClockController::reset
 * @see app/Http/Controllers/Web/Admin/ClockController.php:45
 * @route '/admin/clock/reset'
 */
export const reset = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

reset.definition = {
    methods: ["post"],
    url: '/admin/clock/reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\Admin\ClockController::reset
 * @see app/Http/Controllers/Web/Admin/ClockController.php:45
 * @route '/admin/clock/reset'
 */
reset.url = (options?: RouteQueryOptions) => {
    return reset.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Admin\ClockController::reset
 * @see app/Http/Controllers/Web/Admin/ClockController.php:45
 * @route '/admin/clock/reset'
 */
reset.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reset.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\Admin\ClockController::reset
 * @see app/Http/Controllers/Web/Admin/ClockController.php:45
 * @route '/admin/clock/reset'
 */
    const resetForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reset.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Admin\ClockController::reset
 * @see app/Http/Controllers/Web/Admin/ClockController.php:45
 * @route '/admin/clock/reset'
 */
        resetForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reset.url(options),
            method: 'post',
        })
    
    reset.form = resetForm
const clock = {
    advance: Object.assign(advance, advance),
reset: Object.assign(reset, reset),
}

export default clock