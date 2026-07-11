import PublicApiController from './PublicApiController'
import PaymentWebhookController from './PaymentWebhookController'
import BookingApiController from './BookingApiController'
import OperationsApiController from './OperationsApiController'
import AdminOpsApiController from './AdminOpsApiController'
const Api = {
    PublicApiController: Object.assign(PublicApiController, PublicApiController),
PaymentWebhookController: Object.assign(PaymentWebhookController, PaymentWebhookController),
BookingApiController: Object.assign(BookingApiController, BookingApiController),
OperationsApiController: Object.assign(OperationsApiController, OperationsApiController),
AdminOpsApiController: Object.assign(AdminOpsApiController, AdminOpsApiController),
}

export default Api