import clock from './clock'
import promos from './promos'
import vouchers from './vouchers'
import categories from './categories'

const admin = {
    clock: Object.assign(clock, clock),
    promos: Object.assign(promos, promos),
    vouchers: Object.assign(vouchers, vouchers),
    categories: Object.assign(categories, categories),
}

export default admin