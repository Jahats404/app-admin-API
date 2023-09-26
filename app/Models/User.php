<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use App\Models\Logactivity;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'password',
        'confirm_password',
        'level'
    ];
    public $timestamps = true;

    public function Logactivity(){
        return $this->hasOne(Logactivity::class,'user_id','id');
    }
}
