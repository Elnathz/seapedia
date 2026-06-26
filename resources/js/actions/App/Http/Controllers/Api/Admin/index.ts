import DashboardController from './DashboardController'
import ClockController from './ClockController'
import PromoController from './PromoController'
import VoucherController from './VoucherController'

const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    ClockController: Object.assign(ClockController, ClockController),
    PromoController: Object.assign(PromoController, PromoController),
    VoucherController: Object.assign(VoucherController, VoucherController),
}

export default Admin