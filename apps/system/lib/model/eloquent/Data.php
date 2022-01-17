<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $table = 'data';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
