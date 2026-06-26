import clock from './clock'
import promos from './promos'
import vouchers from './vouchers'

const admin = {
    clock: Object.assign(clock, clock),
    promos: Object.assign(promos, promos),
    vouchers: Object.assign(vouchers, vouchers),
}

export default admin