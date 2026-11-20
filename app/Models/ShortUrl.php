<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    protected $table = 'short_urls';

    protected $primaryKey = 'id';

    protected $fillable = [
        'company_id',
        'title',
        'original_url',
        'is_enabled',
        'is_deleted',
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public static function userCanCreate($user)
    {
        return $user->hasRole('Admin') || $user->hasRole('Member');
    }
}
