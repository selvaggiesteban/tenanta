<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'position',
        'status',
        'source',
        'estimated_value',
        'notes',
        'assigned_to',
        'pipeline_id',
        'pipeline_stage_id',
        'converted_client_id',
        'converted_at',
        'created_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    const STATUSES = [
        'new' => 'Nuevo',
        'contacted' => 'Contactado',
        'qualified' => 'Calificado',
        'proposal' => 'Propuesta',
        'negotiation' => 'Negociación',
        'won' => 'Ganado',
        'lost' => 'Perdido',
    ];

    const SOURCES = [
        'web' => 'Sitio Web',
        'referral' => 'Referido',
        'cold_call' => 'Llamada en Frío',
        'social_media' => 'Redes Sociales',
        'email_campaign' => 'Campaña Email',
        'event' => 'Evento',
        'other' => 'Otro',
    ];

    // Relationships

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    // Scopes

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_client_id');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
              ->orWhere('contact_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Helpers

    public function isConverted(): bool
    {
        return $this->converted_client_id !== null;
    }

    public function canBeConverted(): bool
    {
        return !$this->isConverted() && !in_array($this->status, ['lost']);
    }

    /**
     * Convert lead to client
     */
    public function convertToClient(?string $companyName = null): Client
    {
        if (!$this->canBeConverted()) {
            throw new \Exception('Este lead no puede ser convertido');
        }

        return DB::transaction(function () use ($companyName) {
            // Create client
            $client = Client::create([
                'tenant_id' => $this->tenant_id,
                'name' => $companyName ?? $this->company_name ?? $this->contact_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'created_by' => auth('api')->id(),
            ]);

            // Create contact from lead info
            Contact::create([
                'tenant_id' => $this->tenant_id,
                'client_id' => $client->id,
                'name' => $this->contact_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'position' => $this->position,
                'is_primary' => true,
            ]);

            // Update lead as converted
            $this->update([
                'status' => 'won',
                'converted_client_id' => $client->id,
                'converted_at' => now(),
            ]);

            return $client;
        });
    }

    public function markAsLost(?string $reason = null): void
    {
        $notes = $this->notes;
        if ($reason) {
            $notes = ($notes ? $notes . "\n\n" : '') . "Razón de pérdida: " . $reason;
        }

        $this->update([
            'status' => 'lost',
            'notes' => $notes,
        ]);
    }
}
