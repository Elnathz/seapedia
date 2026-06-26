import wallet from './wallet'
import addresses from './addresses'
import cart from './cart'
import checkout from './checkout'
import orders from './orders'
import reports from './reports'

const buyer = {
    wallet: Object.assign(wallet, wallet),
    addresses: Object.assign(addresses, addresses),
    cart: Object.assign(cart, cart),
    checkout: Object.assign(checkout, checkout),
    orders: Object.assign(orders, orders),
    reports: Object.assign(reports, reports),
}

export default buyer