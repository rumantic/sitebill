<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'component';
    protected $primaryKey = 'component_id';
    public $timestamps = false;
}
