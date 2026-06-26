import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import role from './role'
import catalog from './catalog'
import buyer from './buyer'
import seller from './seller'
import driver from './driver'
import admin from './admin'
/**
* @see \App\Http\Controllers\Api\AuthController::login
* @see app/Http/Controllers/Api/AuthController.php:45
* @route '/api/v1/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/v1/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AuthController::login
* @see app/Http/Controllers/Api/AuthController.php:45
* @route '/api/v1/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AuthController::login
* @see app/Http/Controllers/Api/AuthController.php:45
* @route '/api/v1/login'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::login
* @see app/Http/Controllers/Api/AuthController.php:45
* @route '/api/v1/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::login
* @see app/Http/Controllers/Api/AuthController.php:45
* @route '/api/v1/login'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

/**
* @see \App\Http\Controllers\Api\AuthController::logout
* @see app/Http/Controllers/Api/AuthController.php:76
* @route '/api/v1/logout'
*/
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/api/v1/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AuthController::logout
* @see app/Http/Controllers/Api/AuthController.php:76
* @route '/api/v1/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AuthController::logout
* @see app/Http/Controllers/Api/AuthController.php:76
* @route '/api/v1/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::logout
* @see app/Http/Controllers/Api/AuthController.php:76
* @route '/api/v1/logout'
*/
const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::logout
* @see app/Http/Controllers/Api/AuthController.php:76
* @route '/api/v1/logout'
*/
logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

logout.form = logoutForm

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
export const me = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: '/api/v1/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
me.url = (options?: RouteQueryOptions) => {
    return me.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
me.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
me.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
const meForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
meForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::me
* @see app/Http/Controllers/Api/MeController.php:25
* @route '/api/v1/me'
*/
meForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

me.form = meForm

const v1 = {
    login: Object.assign(login, login),
    logout: Object.assign(logout, logout),
    role: Object.assign(role, role),
    me: Object.assign(me, me),
    catalog: Object.assign(catalog, catalog),
    buyer: Object.assign(buyer, buyer),
    seller: Object.assign(seller, seller),
    driver: Object.assign(driver, driver),
    admin: Object.assign(admin, admin),
}

export default v1