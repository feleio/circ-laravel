<?php

namespace App\Models\Trello;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'mysql_trello';
    
    public function list()
    {
        return $this->belongsTo('App\Models\Trello\List');
    }
}
    
