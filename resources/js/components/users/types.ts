export type UserRow = {
    id: number;
    name: string;
    username: string;
    email: string;
    roles: string[];
    office: { id: number; name: string } | null;
    deleted_at: string | null;
    created_at: string | null;
    is_self: boolean;
};

export type UserSortKey = 'name' | 'username' | 'email' | 'role' | 'office' | 'status' | 'created';

export type UserStatus = 'active' | 'deleted' | 'all';

export type UserFilters = {
    search: string | null;
    role: string | null;
    office: string | null;
    status: UserStatus;
    sort: UserSortKey;
    direction: 'asc' | 'desc';
    per_page: number;
};

export type UserPage = {
    data: UserRow[];
    meta: { current_page: number; last_page: number; from: number | null; to: number | null; total: number; per_page: number };
};
