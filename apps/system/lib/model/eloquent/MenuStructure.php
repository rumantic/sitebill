<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class MenuStructure extends Model
{
    protected $table = 'menu_structure';
    protected $primaryKey = 'menu_structure_id';
    public $timestamps = false;
}
