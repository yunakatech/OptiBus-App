<?php

/**
 * QBUS SaaS Configuration
 *
 * Controls SaaS-specific behavior across the application.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Gating
    |--------------------------------------------------------------------------
    |
    | When enabled, FeatureGate checks enforce plan-based feature restrictions.
    | Set to false during initial SaaS migration to keep all features open.
    |
    */
    'feature_gating_enabled' => env('SAAS_FEATURE_GATING_ENABLED', env('SAAS_FEATURE_GATING', false)),

    /*
    |--------------------------------------------------------------------------
    | Trial Settings
    |--------------------------------------------------------------------------
    |
    | Default trial period for new tenants (in days).
    |
    */
    'trial_days' => (int) env('SAAS_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Days after subscription expiry before suspension (in days).
    |
    */
    'grace_period_days' => (int) env('SAAS_GRACE_PERIOD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Days to keep tenant data after cancellation before soft-delete (in days).
    |
    */
    'data_retention_days' => (int) env('SAAS_DATA_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | Default plan slug assigned to new tenants during registration.
    |
    */
    'default_plan' => env('SAAS_DEFAULT_PLAN', 'starter'),

    /*
    |--------------------------------------------------------------------------
    | Platform Admin
    |--------------------------------------------------------------------------
    |
    | Permission key required to access the platform admin dashboard.
    |
    */
    'platform_admin_permission' => env('SAAS_PLATFORM_ADMIN_PERMISSION', 'pool.manage'),

    /*
    |--------------------------------------------------------------------------
    | Inertia Shared Data
    |--------------------------------------------------------------------------
    |
    | Whether to inject tenant subscription info into every Inertia response.
    |
    */
    'inject_subscription_data' => (bool) env('SAAS_INJECT_SUBSCRIPTION_DATA', true),
];
