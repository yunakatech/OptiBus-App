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

export type Auth = {
    user: User;
    permissions: string[];
    pool_scope?: PoolScope | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
