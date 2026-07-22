<?php


namespace system\lib\model\eloquent;

use Illuminate\Database\Eloquent\Model;

class ImageParsingQueue extends Model
{
    protected $table = 'image_parsing_queue';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
