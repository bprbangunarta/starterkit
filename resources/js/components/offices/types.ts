export type OfficeRow = {
    id: number;
    name: string;
    address: string | null;
    latitude: string | null;
    longitude: string | null;
    users_count: number;
    created_at: string | null;
};

export type OfficeSortKey = 'name' | 'address' | 'latitude' | 'longitude' | 'users' | 'created';

export type OfficeFilters = {
    search: string | null;
    sort: OfficeSortKey;
    direction: 'asc' | 'desc';
    per_page: number;
};

export type OfficePage = {
    data: OfficeRow[];
    meta: { current_page: number; last_page: number; from: number | null; to: number | null; total: number; per_page: number };
};

/** An office to choose from in another form. */
export type OfficeOption = { id: number; name: string };
