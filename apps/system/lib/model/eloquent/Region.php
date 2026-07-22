<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'region';
    protected $primaryKey = 'region_id';
    public $timestamps = false;
}
