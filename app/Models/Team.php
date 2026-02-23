<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function addMember(User $user): void
    {
        $this->members()->syncWithoutDetaching([$user->id]);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
