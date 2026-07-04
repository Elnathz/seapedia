import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/role/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\RoleController::create
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Web\RoleController::store
 * @see app/Http/Controllers/Web/RoleController.php:33
 * @route '/role/select'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/role/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\RoleController::store
 * @see app/Http/Controllers/Web/RoleController.php:33
 * @route '/role/select'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::store
 * @see app/Http/Controllers/Web/RoleController.php:33
 * @route '/role/select'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\RoleController::store
 * @see app/Http/Controllers/Web/RoleController.php:33
 * @route '/role/select'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\RoleController::store
 * @see app/Http/Controllers/Web/RoleController.php:33
 * @route '/role/select'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Web\RoleController::add
 * @see app/Http/Controllers/Web/RoleController.php:49
 * @route '/role/add'
 */
export const add = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(options),
    method: 'post',
})

add.definition = {
    methods: ["post"],
    url: '/role/add',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\RoleController::add
 * @see app/Http/Controllers/Web/RoleController.php:49
 * @route '/role/add'
 */
add.url = (options?: RouteQueryOptions) => {
    return add.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::add
 * @see app/Http/Controllers/Web/RoleController.php:49
 * @route '/role/add'
 */
add.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\RoleController::add
 * @see app/Http/Controllers/Web/RoleController.php:49
 * @route '/role/add'
 */
    const addForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: add.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\RoleController::add
 * @see app/Http/Controllers/Web/RoleController.php:49
 * @route '/role/add'
 */
        addForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: add.url(options),
            method: 'post',
        })
    
    add.form = addForm
const RoleController = { create, store, add }

export default RoleController