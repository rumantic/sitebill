<?php

namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class KPI extends Model
{
    protected $table = 'kpi';
    protected $primaryKey = 'kpi_id';
    public $timestamps = true;

}
