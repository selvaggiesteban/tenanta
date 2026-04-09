<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SuspendInactiveUsersCommand extends Command
{
    protected $signature = 'tenanta:suspend-inactive';
    protected $description = 'Suspende cuentas que no activaron su perfil en los primeros 2 días';

    public function handle(): void
    {
        $limitDate = Carbon::now()->subDays(2);

        $affected = User::where('status', 'pending_activation')
            ->where('created_at', '<=', $limitDate)
            ->whereNull('password_updated_at')
            ->update(['status' => 'suspended']);

        $this->info("Proceso completado. Usuarios suspendidos: {$affected}");
    }
}
