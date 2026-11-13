type Common = {
    /** What the screen calls the storage: the name a person gave it, or the name of its kind. */
    label: string;
    /** The name of its kind: "Lokal", "Amazon S3", or "SFTP". */
    kind: string;
    /** Whether the connection can be set on this screen; the local storage is the project's own folder and cannot. */
    editable: boolean;
    /** Whether this is the storage that new files are saved to (chosen on the Pengaturan screen). */
    active: boolean;
    /** The name a person gave the storage, if any. */
    name: string | null;
    /** Where the connection in force comes from: the server environment, or what was saved on this screen. */
    source: 'env' | 'screen';
    configured: boolean;
    summary: string | null;
    tested_at: string | null;
    test_ok: boolean | null;
    test_message: string | null;
};

export type LocalEntry = Common & { type: 'local'; form: Record<string, never>; secrets: Record<string, never> };

export type S3Entry = Common & {
    type: 's3';
    form: { region: string; bucket: string; endpoint: string; use_path_style: boolean; root: string };
    /** Only whether a secret is set, and the last four characters of the access key. */
    secrets: { key_hint: string | null; secret_set: boolean };
};

export type SftpEntry = Common & {
    type: 'sftp';
    form: { host: string; port: string; username: string; root: string };
    secrets: { password_set: boolean; private_key_set: boolean };
};

export type StorageEntry = LocalEntry | S3Entry | SftpEntry;
