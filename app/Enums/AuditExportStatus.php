<?php

namespace App\Enums;

/** Where an audit export is: waiting for the queue, a file ready to download, or given up after its retries. */
enum AuditExportStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Failed = 'failed';
}
