export type User = {
    id: number;
    name: string;
    username?: string;
    email: string;
    phone?: string | null;
    avatar?: string;
    avatar_path?: string | null;
    is_admin?: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type RoleName = 'buyer' | 'seller' | 'driver';

export type Auth = {
    user: User | null;
    roles: RoleName[];
    activeRole: RoleName | null;
};
