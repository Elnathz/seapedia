import ClockController from './ClockController'
import PromoController from './PromoController'
import VoucherController from './VoucherController'
import CategoryController from './CategoryController'
import AdminUserController from './AdminUserController'
import AdminStoreController from './AdminStoreController'
import AdminProductController from './AdminProductController'
import AdminOrderController from './AdminOrderController'
import AdminDeliveryController from './AdminDeliveryController'
import AdminOverdueController from './AdminOverdueController'
import BannerController from './BannerController'

const Admin = {
    ClockController: Object.assign(ClockController, ClockController),
    PromoController: Object.assign(PromoController, PromoController),
    VoucherController: Object.assign(VoucherController, VoucherController),
    CategoryController: Object.assign(CategoryController, CategoryController),
    AdminUserController: Object.assign(AdminUserController, AdminUserController),
    AdminStoreController: Object.assign(AdminStoreController, AdminStoreController),
    AdminProductController: Object.assign(AdminProductController, AdminProductController),
    AdminOrderController: Object.assign(AdminOrderController, AdminOrderController),
    AdminDeliveryController: Object.assign(AdminDeliveryController, AdminDeliveryController),
    AdminOverdueController: Object.assign(AdminOverdueController, AdminOverdueController),
    BannerController: Object.assign(BannerController, BannerController),
}

export default Admin