<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'order_number', 'status', 
        'total_amount', 'payment_method', 'payment_id',
        'billing_name', 'billing_tax_id', 'billing_address'
    ];
}
