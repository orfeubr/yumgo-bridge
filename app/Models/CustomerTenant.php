<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * CustomerTenant Pivot Model
 * 
 * Relacionamento entre Customer (central) e Tenant.
 * Armazena dados específicos do relacionamento:
 * - Cashback por restaurante
 * - Tier de fidelidade por restaurante
 * - Total de pedidos/gastos por restaurante
 */
class CustomerTenant extends Pivot
{
    protected $table = 'customer_tenant';
    protected $connection = 'pgsql'; // Schema PUBLIC

    protected $fillable = [
        'customer_id',
        'tenant_id',
        'cashback_balance',
        'loyalty_tier',
        'total_orders',
        'total_spent',
        'first_order_at',
        'last_order_at',
        'is_active',
    ];

    protected $casts = [
        'cashback_balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'first_order_at' => 'datetime',
        'last_order_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Adicionar cashback
     */
    public function addCashback($amount)
    {
        $this->increment('cashback_balance', $amount);
    }

    /**
     * Usar cashback
     */
    public function useCashback($amount)
    {
        if ($this->cashback_balance >= $amount) {
            $this->decrement('cashback_balance', $amount);
            return true;
        }
        return false;
    }

    /**
     * Registrar novo pedido
     */
    public function registerOrder($orderValue)
    {
        $this->increment('total_orders');
        $this->increment('total_spent', $orderValue);
        
        if (!$this->first_order_at) {
            $this->first_order_at = now();
        }
        
        $this->last_order_at = now();
        $this->save();
    }
}
