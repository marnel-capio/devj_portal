<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module',
        'subject',
        'to',
        'to_name',
        'from',
        'from_name',
        'body',
        'created_by',
        'updated_by',
        'create_time',
        'update_time',
    ];

    protected $table = 'mail_history';
    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
}
