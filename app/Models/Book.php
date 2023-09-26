<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'book';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;

}
