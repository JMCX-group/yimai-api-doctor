<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionRecord extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transaction_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'name',
        'price',
        'type',
        'status'
    ];
}
