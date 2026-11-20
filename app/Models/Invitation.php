<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $table = 'invitations';

    protected $primaryKey = 'id';

    protected $fillable = [
        'company_id',
        'user_id',
        'role_name',
        'invited_by',
        'is_enabled',
        'is_deleted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by', 'id');
    }
}
