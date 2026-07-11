import payments from './payments'
import routes from './routes'
import schedules from './schedules'
import drivers from './drivers'
import luggageServices from './luggage-services'
import segments from './segments'
import customers from './customers'
import cancellations from './cancellations'
import armadas from './armadas'
import reports from './reports'
import charters from './charters'
import luggages from './luggages'
import assignments from './assignments'
import customerBagasi from './customer-bagasi'
import customerCharter from './customer-charter'
import charterRoutes from './charter-routes'
import units from './units'
import armadaCategories from './armada-categories'
import pools from './pools'
import tenant from './tenant'
import pool from './pool'
import users from './users'
import roles from './roles'
import tenants from './tenants'
import subscriptions from './subscriptions'
import plans from './plans'
import invoices from './invoices'
import paymentSettings from './payment-settings'
const admin = {
    payments: Object.assign(payments, payments),
routes: Object.assign(routes, routes),
schedules: Object.assign(schedules, schedules),
drivers: Object.assign(drivers, drivers),
luggageServices: Object.assign(luggageServices, luggageServices),
segments: Object.assign(segments, segments),
customers: Object.assign(customers, customers),
cancellations: Object.assign(cancellations, cancellations),
armadas: Object.assign(armadas, armadas),
reports: Object.assign(reports, reports),
charters: Object.assign(charters, charters),
luggages: Object.assign(luggages, luggages),
assignments: Object.assign(assignments, assignments),
customerBagasi: Object.assign(customerBagasi, customerBagasi),
customerCharter: Object.assign(customerCharter, customerCharter),
charterRoutes: Object.assign(charterRoutes, charterRoutes),
units: Object.assign(units, units),
armadaCategories: Object.assign(armadaCategories, armadaCategories),
pools: Object.assign(pools, pools),
tenant: Object.assign(tenant, tenant),
pool: Object.assign(pool, pool),
users: Object.assign(users, users),
roles: Object.assign(roles, roles),
tenants: Object.assign(tenants, tenants),
subscriptions: Object.assign(subscriptions, subscriptions),
plans: Object.assign(plans, plans),
invoices: Object.assign(invoices, invoices),
paymentSettings: Object.assign(paymentSettings, paymentSettings),
}

export default admin