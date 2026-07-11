import GoogleAuthController from './GoogleAuthController'
import OnboardingController from './OnboardingController'
const Auth = {
    GoogleAuthController: Object.assign(GoogleAuthController, GoogleAuthController),
OnboardingController: Object.assign(OnboardingController, OnboardingController),
}

export default Auth