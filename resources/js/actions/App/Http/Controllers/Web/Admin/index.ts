import ClockController from './ClockController'
import PromoController from './PromoController'
import VoucherController from './VoucherController'
import CategoryController from './CategoryController'

const Admin = {
    ClockController: Object.assign(ClockController, ClockController),
    PromoController: Object.assign(PromoController, PromoController),
    VoucherController: Object.assign(VoucherController, VoucherController),
    CategoryController: Object.assign(CategoryController, CategoryController),
}

export default Admin