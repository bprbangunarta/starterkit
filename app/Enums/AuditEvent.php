<?php

namespace App\Enums;

/** The fixed vocabulary of audit events. The values are what the `event` column stores. */
enum AuditEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Lockout = 'lockout';
    case Logout = 'logout';
    case PasswordChangeFailed = 'password_change_failed';
    case Exported = 'exported';
    case RoleChanged = 'role_changed';
    case PasswordReset = 'password_reset';
    case PasswordConfirmFailed = 'password_confirm_failed';
    case StorageTested = 'storage_tested';
    case FileDownloaded = 'file_downloaded';
    case FilePreviewed = 'file_previewed';

    /** The module an event belongs to when the caller does not name one. */
    public function defaultModule(): string
    {
        return match ($this) {
            self::Login,
            self::LoginFailed,
            self::Lockout,
            self::Logout,
            self::PasswordChangeFailed,
            self::PasswordConfirmFailed => 'Autentikasi',
            self::Exported => 'Audit',
            self::StorageTested, self::FileDownloaded, self::FilePreviewed => 'Penyimpanan',
            self::RoleChanged, self::PasswordReset => 'Pengguna',
            default => 'Sistem',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Dibuat',
            self::Updated => 'Diubah',
            self::Deleted => 'Dihapus',
            self::Restored => 'Dipulihkan',
            self::Login => 'Masuk',
            self::LoginFailed => 'Gagal masuk',
            self::Lockout => 'Terkunci',
            self::Logout => 'Keluar',
            self::PasswordChangeFailed => 'Gagal ganti kata sandi',
            self::Exported => 'Diekspor',
            self::RoleChanged => 'Jabatan diubah',
            self::PasswordReset => 'Kata sandi diatur ulang',
            self::PasswordConfirmFailed => 'Gagal konfirmasi kata sandi',
            self::StorageTested => 'Uji koneksi',
            self::FileDownloaded => 'Berkas diunduh',
            self::FilePreviewed => 'Berkas dilihat',
        };
    }
}
