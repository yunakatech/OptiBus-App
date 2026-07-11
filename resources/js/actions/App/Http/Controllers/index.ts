import Auth from './Auth'
import PublicController from './PublicController'
import Api from './Api'
import StaticAssetController from './StaticAssetController'
import PlatformDashboardController from './PlatformDashboardController'
import UserPreferenceController from './UserPreferenceController'
import DashboardController from './DashboardController'
import BookingController from './BookingController'
import PaymentController from './PaymentController'
import AdminOpsFlowsController from './AdminOpsFlowsController'
import CharterDocumentController from './CharterDocumentController'
import LuggageDocumentController from './LuggageDocumentController'
import AdminOpsController from './AdminOpsController'
import AdminOpsMasterController from './AdminOpsMasterController'
import AdminOpsSaasController from './AdminOpsSaasController'
import SoloDriverController from './SoloDriverController'
import SubscriptionPaymentController from './SubscriptionPaymentController'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
PublicController: Object.assign(PublicController, PublicController),
Api: Object.assign(Api, Api),
StaticAssetController: Object.assign(StaticAssetController, StaticAssetController),
PlatformDashboardController: Object.assign(PlatformDashboardController, PlatformDashboardController),
UserPreferenceController: Object.assign(UserPreferenceController, UserPreferenceController),
DashboardController: Object.assign(DashboardController, DashboardController),
BookingController: Object.assign(BookingController, BookingController),
PaymentController: Object.assign(PaymentController, PaymentController),
AdminOpsFlowsController: Object.assign(AdminOpsFlowsController, AdminOpsFlowsController),
CharterDocumentController: Object.assign(CharterDocumentController, CharterDocumentController),
LuggageDocumentController: Object.assign(LuggageDocumentController, LuggageDocumentController),
AdminOpsController: Object.assign(AdminOpsController, AdminOpsController),
AdminOpsMasterController: Object.assign(AdminOpsMasterController, AdminOpsMasterController),
AdminOpsSaasController: Object.assign(AdminOpsSaasController, AdminOpsSaasController),
SoloDriverController: Object.assign(SoloDriverController, SoloDriverController),
SubscriptionPaymentController: Object.assign(SubscriptionPaymentController, SubscriptionPaymentController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers