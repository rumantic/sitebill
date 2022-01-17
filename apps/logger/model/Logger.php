<?php

namespace logger\model;

use Illuminate\Database\Eloquent\Model;

class Logger extends Model
{
    protected $table = 'logger';
    protected $primaryKey = 'logger_id';
    public $timestamps = false;
}
