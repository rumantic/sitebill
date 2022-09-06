<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $table = 'cache';
    protected $primaryKey = 'parameter';
    public $timestamps = false;

}
