<?php

namespace lang_words\model;

use Illuminate\Database\Eloquent\Model;

class LangWords extends Model
{
    protected $table = 'lang_words';
    protected $primaryKey = 'word_id';
    public $timestamps = false;
}
