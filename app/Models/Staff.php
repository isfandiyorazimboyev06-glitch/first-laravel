<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [

            'id',
            'fio',
            'telephone_number',
            'created_at',
            'updated_at',
            'organization_id',
            'active',
            'status',
    ];

}




