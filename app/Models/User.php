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
use Illuminate\Support\Facades\Auth;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = true;

    public static function getLoggedInUserId()
    {
        if (Auth::check()) {
            return Auth::id();
        }

        return null; // Pengguna tidak sedang login
    }

    public function Logactivity(){
        return $this->hasOne(Logactivity::class,'user_id','id');
    }
}
