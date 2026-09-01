import PublicBookingApiController from './PublicBookingApiController'
import PublicApiController from './PublicApiController'
import PaymentWebhookController from './PaymentWebhookController'
import AdminOpsApiController from './AdminOpsApiController'
import BookingApiController from './BookingApiController'
import OperationsApiController from './OperationsApiController'
import AdminPublicBookingApiController from './AdminPublicBookingApiController'
const Api = {
    PublicBookingApiController: Object.assign(PublicBookingApiController, PublicBookingApiController),
PublicApiController: Object.assign(PublicApiController, PublicApiController),
PaymentWebhookController: Object.assign(PaymentWebhookController, PaymentWebhookController),
AdminOpsApiController: Object.assign(AdminOpsApiController, AdminOpsApiController),
BookingApiController: Object.assign(BookingApiController, BookingApiController),
OperationsApiController: Object.assign(OperationsApiController, OperationsApiController),
AdminPublicBookingApiController: Object.assign(AdminPublicBookingApiController, AdminPublicBookingApiController),
}

export default Api