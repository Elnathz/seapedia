import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/driver/jobs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\Driver\JobController::index
 * @see app/Http/Controllers/Api/Driver/JobController.php:24
 * @route '/api/v1/driver/jobs'
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
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
export const show = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/driver/jobs/{delivery}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
show.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { delivery: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.id
                : args.delivery,
                }

    return show.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
show.get = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
show.head = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
    const showForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
        showForm.get = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\Driver\JobController::show
 * @see app/Http/Controllers/Api/Driver/JobController.php:36
 * @route '/api/v1/driver/jobs/{delivery}'
 */
        showForm.head = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Api\Driver\JobController::take
 * @see app/Http/Controllers/Api/Driver/JobController.php:59
 * @route '/api/v1/driver/jobs/{delivery}/take'
 */
export const take = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: take.url(args, options),
    method: 'post',
})

take.definition = {
    methods: ["post"],
    url: '/api/v1/driver/jobs/{delivery}/take',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\Driver\JobController::take
 * @see app/Http/Controllers/Api/Driver/JobController.php:59
 * @route '/api/v1/driver/jobs/{delivery}/take'
 */
take.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { delivery: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.id
                : args.delivery,
                }

    return take.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Driver\JobController::take
 * @see app/Http/Controllers/Api/Driver/JobController.php:59
 * @route '/api/v1/driver/jobs/{delivery}/take'
 */
take.post = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: take.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\Driver\JobController::take
 * @see app/Http/Controllers/Api/Driver/JobController.php:59
 * @route '/api/v1/driver/jobs/{delivery}/take'
 */
    const takeForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: take.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\Driver\JobController::take
 * @see app/Http/Controllers/Api/Driver/JobController.php:59
 * @route '/api/v1/driver/jobs/{delivery}/take'
 */
        takeForm.post = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: take.url(args, options),
            method: 'post',
        })
    
    take.form = takeForm
/**
* @see \App\Http\Controllers\Api\Driver\JobController::complete
 * @see app/Http/Controllers/Api/Driver/JobController.php:79
 * @route '/api/v1/driver/jobs/{delivery}/complete'
 */
export const complete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
})

complete.definition = {
    methods: ["post"],
    url: '/api/v1/driver/jobs/{delivery}/complete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\Driver\JobController::complete
 * @see app/Http/Controllers/Api/Driver/JobController.php:79
 * @route '/api/v1/driver/jobs/{delivery}/complete'
 */
complete.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { delivery: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.id
                : args.delivery,
                }

    return complete.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\Driver\JobController::complete
 * @see app/Http/Controllers/Api/Driver/JobController.php:79
 * @route '/api/v1/driver/jobs/{delivery}/complete'
 */
complete.post = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\Driver\JobController::complete
 * @see app/Http/Controllers/Api/Driver/JobController.php:79
 * @route '/api/v1/driver/jobs/{delivery}/complete'
 */
    const completeForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: complete.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\Driver\JobController::complete
 * @see app/Http/Controllers/Api/Driver/JobController.php:79
 * @route '/api/v1/driver/jobs/{delivery}/complete'
 */
        completeForm.post = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: complete.url(args, options),
            method: 'post',
        })
    
    complete.form = completeForm
const jobs = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
take: Object.assign(take, take),
complete: Object.assign(complete, complete),
}

export default jobs