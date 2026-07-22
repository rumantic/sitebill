<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
}
