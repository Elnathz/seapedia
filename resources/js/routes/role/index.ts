import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
export const select = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})

select.definition = {
    methods: ["get","head"],
    url: '/role/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
select.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
select.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: select.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
    const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: select.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
        selectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: select.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\RoleController::select
 * @see app/Http/Controllers/Web/RoleController.php:23
 * @route '/role/select'
 */
        selectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: select.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    select.form = selectForm
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
/**
* @see \App\Http\Controllers\Web\Profile\RoleDeleteController::destroy
 * @see app/Http/Controllers/Web/Profile/RoleDeleteController.php:19
 * @route '/role/{role}'
 */
export const destroy = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/role/{role}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Web\Profile\RoleDeleteController::destroy
 * @see app/Http/Controllers/Web/Profile/RoleDeleteController.php:19
 * @route '/role/{role}'
 */
destroy.url = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { role: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    role: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        role: args.role,
                }

    return destroy.definition.url
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\Profile\RoleDeleteController::destroy
 * @see app/Http/Controllers/Web/Profile/RoleDeleteController.php:19
 * @route '/role/{role}'
 */
destroy.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Web\Profile\RoleDeleteController::destroy
 * @see app/Http/Controllers/Web/Profile/RoleDeleteController.php:19
 * @route '/role/{role}'
 */
    const destroyForm = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\Profile\RoleDeleteController::destroy
 * @see app/Http/Controllers/Web/Profile/RoleDeleteController.php:19
 * @route '/role/{role}'
 */
        destroyForm.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const role = {
    select: Object.assign(select, select),
store: Object.assign(store, store),
add: Object.assign(add, add),
destroy: Object.assign(destroy, destroy),
}

export default role