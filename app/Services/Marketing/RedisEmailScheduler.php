<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Redis;

class RedisEmailScheduler
{
    protected $prefix = 'email_warmup:';

    /**
     * Registra el estado de una cuenta SMTP en la curva de warm-up.
     */
    public function updateAccountState(string $email, int $currentInterval, string $campaignId)
    {
        $key = $this->prefix . $email;
        Redis::hmset($key, [
            'interval' => $currentInterval,
            'campaign_id' => $campaignId,
            'last_sent_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Obtiene el siguiente intervalo (1-10-1 min) para una cuenta.
     */
    public function getNextInterval(string $email): int
    {
        $state = Redis::hgetall($this->prefix . $email);
        $current = (int) ($state['interval'] ?? 0);
        
        // Lógica de curva escalonada (simplificada para el ejemplo)
        if ($current < 10) {
            return $current + 1;
        }
        
        return 1; // Reinicia el ciclo o baja según la lógica del prompt
    }
}
