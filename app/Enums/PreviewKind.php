<?php

namespace App\Enums;

/** How the browser shows a file that can be previewed. */
enum PreviewKind: string
{
    case Image = 'image';
    case Pdf = 'pdf';
    case Text = 'text';

    /** The policy of the response that carries the file: nothing may load from it, and everything but a PDF (whose viewer needs a frame without sandbox) is sandboxed. */
    public function contentSecurityPolicy(): string
    {
        return match ($this) {
            self::Pdf => "default-src 'none'; frame-ancestors 'self'",
            default => "default-src 'none'; sandbox; frame-ancestors 'self'",
        };
    }
}
