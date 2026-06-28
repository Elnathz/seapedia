import AuthController from './AuthController'
import RoleController from './RoleController'
import MeController from './MeController'
import CatalogController from './CatalogController'
import BuyerWalletController from './BuyerWalletController'
import BuyerAddressController from './BuyerAddressController'
import BuyerCartController from './BuyerCartController'
import CheckoutController from './CheckoutController'
import BuyerOrderController from './BuyerOrderController'
import BuyerReportController from './BuyerReportController'
import Seller from './Seller'
import SellerReportController from './SellerReportController'
import Driver from './Driver'
import Admin from './Admin'
const Api = {
    AuthController: Object.assign(AuthController, AuthController),
RoleController: Object.assign(RoleController, RoleController),
MeController: Object.assign(MeController, MeController),
CatalogController: Object.assign(CatalogController, CatalogController),
BuyerWalletController: Object.assign(BuyerWalletController, BuyerWalletController),
BuyerAddressController: Object.assign(BuyerAddressController, BuyerAddressController),
BuyerCartController: Object.assign(BuyerCartController, BuyerCartController),
CheckoutController: Object.assign(CheckoutController, CheckoutController),
BuyerOrderController: Object.assign(BuyerOrderController, BuyerOrderController),
BuyerReportController: Object.assign(BuyerReportController, BuyerReportController),
Seller: Object.assign(Seller, Seller),
SellerReportController: Object.assign(SellerReportController, SellerReportController),
Driver: Object.assign(Driver, Driver),
Admin: Object.assign(Admin, Admin),
}

export default Api