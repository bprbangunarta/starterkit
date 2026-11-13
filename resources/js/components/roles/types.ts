export type RoleRow = { id: number; name: string; users_count: number; is_base: boolean; created_at: string | null };

export type RoleSortKey = 'name' | 'users' | 'created';

export type RoleFilters = {
    search: string | null;
    sort: RoleSortKey;
    direction: 'asc' | 'desc';
    per_page: number;
};

export type RolePage = {
    data: RoleRow[];
    meta: { current_page: number; last_page: number; from: number | null; to: number | null; total: number; per_page: number };
};
