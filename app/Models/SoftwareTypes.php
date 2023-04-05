<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareTypes extends Model
{
    use HasFactory;
    protected $guarded = [];
    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

}
