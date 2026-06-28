import store from './store'
import products from './products'
import orders from './orders'
import reports from './reports'
const seller = {
    store: Object.assign(store, store),
products: Object.assign(products, products),
orders: Object.assign(orders, orders),
reports: Object.assign(reports, reports),
}

export default seller