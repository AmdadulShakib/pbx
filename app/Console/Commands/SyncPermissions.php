<?php

namespace App\Console\Commands;

use App\Services\Access\PermissionSyncService;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'app:sync-permissions';

    protected $description = 'Sync admin permissions from named routes';

    public function handle(PermissionSyncService $syncService): int
    {
        $keys = $syncService->sync();

        $this->info('Permissions synced: ' . count($keys));

        return self::SUCCESS;
    }
}
