<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class Logactivity extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'log_activity';
    protected $guarded = ['id'];
    public $timestamps = true;

    public function Users(){
        return $this->BelongsTo(Users::class,'user_id','id');
    }


}
