export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_super_admin?: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type PoolScope = {
    all: boolean;
    pool_ids: number[];
    pool_name: string;
    route_ids: number[];
    route_names: string[];
    labels: string[];
};

export type PoolOption = {
    id: number;
    name: string;
    code?: string | null;
};

export type ActivePool = {
    id: number;
    name: string;
};

export type TenantSubscription = {
    subscription_id?: number;
    tenant_id: number;
    tenant_name: string;
    tenant_status?: string;
    plan_id: number;
    plan_name: string;
    plan_slug: string;
    subscription_status: string;
    trial_ends_at: string | null;
    ends_at: string | null;
};

export type Auth = {
    user: User;
    permissions: string[];
    pools?: PoolOption[];
    pool_scope?: PoolScope | null;
    active_pool?: ActivePool | null;
    tenant_subscription?: TenantSubscription | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
