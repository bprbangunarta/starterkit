<?php

return [

    /*
    | Hosts that the storage settings may point to even though they sit on an internal network (a MinIO or an SFTP server inside the
    | bank's network). Any other host must resolve to public addresses only. Separate hosts with commas.
    */
    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', strtolower((string) env('STORAGE_ALLOWED_HOSTS', '')))))),

    // Seconds to wait for a connection and for the whole test call (production-standards: 3 and 10).
    'connect_timeout' => 3,
    'timeout' => 10,

    // The most a call of the file manager may take in total, an upload of the biggest file included.
    'file_timeout' => 60,

    // What the file manager may hold: 10 MB a file (production-standards), and these extensions only.
    'upload_max_kb' => 10240,
    'upload_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip', 'jpg', 'jpeg', 'png', 'webp', 'gif'],

    // The biggest file the file manager shows in the browser; a bigger one is only for download.
    'preview_max_kb' => 5120,

    // What the file manager may show in the browser: extension => [kind, content type]. The type comes from this list, never from the file.
    // SVG and HTML are left out on purpose, since they can run script.
    'preview_types' => [
        'jpg' => ['image', 'image/jpeg'],
        'jpeg' => ['image', 'image/jpeg'],
        'png' => ['image', 'image/png'],
        'webp' => ['image', 'image/webp'],
        'gif' => ['image', 'image/gif'],
        'pdf' => ['pdf', 'application/pdf'],
        'txt' => ['text', 'text/plain; charset=UTF-8'],
        'csv' => ['text', 'text/plain; charset=UTF-8'],
        'json' => ['text', 'text/plain; charset=UTF-8'],
        'log' => ['text', 'text/plain; charset=UTF-8'],
    ],

    // Entries of a folder that one page of the file manager looks at; a bigger folder shows its first ones and says so.
    'listing_max_entries' => 1000,

];
