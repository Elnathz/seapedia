import clock from './clock'
import promos from './promos'
import vouchers from './vouchers'
import categories from './categories'
import users from './users'
import stores from './stores'
import products from './products'
import orders from './orders'
import deliveries from './deliveries'
import overdue from './overdue'
import banners from './banners'
const admin = {
    clock: Object.assign(clock, clock),
promos: Object.assign(promos, promos),
vouchers: Object.assign(vouchers, vouchers),
categories: Object.assign(categories, categories),
users: Object.assign(users, users),
stores: Object.assign(stores, stores),
products: Object.assign(products, products),
orders: Object.assign(orders, orders),
deliveries: Object.assign(deliveries, deliveries),
overdue: Object.assign(overdue, overdue),
banners: Object.assign(banners, banners),
}

export default admin