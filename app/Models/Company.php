<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    protected $table = 'companies';

    protected $primaryKey = 'id';

    protected $fillable = [
        'company_name',
        'company_code',
        'is_enabled',
        'is_deleted',
        'created_by',
        'updated_by',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function shortUrls()
    {
        return $this->hasMany(ShortUrl::class, 'company_id', 'id');
    }
}
