export type AuditOption = { value: string; label: string };

export type AuditEntry = {
    id: number;
    created_at: string;
    event: { value: string | null; label: string | null };
    result: AuditOption;
    description: string;
    module: string | null;
    causer: { id: number; name: string; username: string | null } | null;
    subject: { type: string; id: number } | null;
    ip_address: string | null;
    request_id: string | null;
    method: string | null;
    path: string | null;
    user_agent: string | null;
    reason: string | null;
    /** Facts that are not a before and after, such as the filters of an export request. */
    details: { label: string; value: string }[];
    /** PHP sends an empty side as `[]`, not `{}`. */
    changes: { old: unknown; attributes: unknown };
};

export type AuditSortKey = 'time' | 'host' | 'user' | 'module' | 'event' | 'record' | 'outcome';

export type AuditFilters = {
    search: string | null;
    module: string | null;
    result: string | null;
    from: string | null;
    to: string | null;
    sort: AuditSortKey;
    direction: 'asc' | 'desc';
    per_page: number;
};

export type AuditPage = {
    data: AuditEntry[];
    meta: { current_page: number; last_page: number; from: number | null; to: number | null; total: number; per_page: number };
};
