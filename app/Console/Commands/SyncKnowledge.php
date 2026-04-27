<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Jobs\AI\SyncKnowledgeIndex;

class SyncKnowledge extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'tenanta:sync-knowledge';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Sincroniza el índice de conocimiento de todos los tenants para el IA Copilot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando sincronización masiva de conocimiento...');

        Tenant::where('is_active', true)->chunk(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $this->line(" - Despachando sincronización para: {$tenant->name}");
                SyncKnowledgeIndex::dispatch($tenant->id);
            }
        });

        $this->info('✅ Todos los trabajos han sido enviados a la cola.');
    }
}
