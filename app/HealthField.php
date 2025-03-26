<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\HealthTypeOperation;

class HealthField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_info', 'invoice_period_start_date', 'invoice_period_end_date', 'health_type_operation_id', 'print_users_info_to_pdf'
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->health_type_operation = $this->health_type_operation();
        if(isset($this->attributes['users_info']))
            $this->users_info = $this->setusers_info($this->attributes['users_info']);
    }

    /**
    * Get the health type operation belongs to
    */
    public function health_type_operation() {
        return HealthTypeOperation::where('id', $this->health_type_operation_id)->firstOrfail();
    }

    /**
     * Set the health fields users info.
     *
     * @param string $value
     */
    public function setusers_info(array $data = [])
    {
        $Users = collect();

        foreach ($data as $value) {
            $Users->push(new HealthUser($value));
        }
        return $Users;
    }
}
