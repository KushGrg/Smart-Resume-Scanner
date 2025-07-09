<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDetail extends Model
{
    //
    use HasFactory;

    protected $table = 'hr_details';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'organization_name',
        'logo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
