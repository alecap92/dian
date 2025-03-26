<?php

namespace App;

use App\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_method_id', 'bank_name', 'account_type', 'account_number',
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->payment_method = $this->payment_method();
    }

    /**
    * Get the payment method payment belongs to
    */
    public function payment_method() {
        return PaymentMethod::where('id', $this->payment_method_id)->firstOrfail();
    }
}
