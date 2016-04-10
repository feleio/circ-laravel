<?php

namespace App\Models\Trello;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'mysql_trello';
    
    public function tasklist()
    {
        return $this->belongsTo('App\Models\Trello\Tasklist');
    }
}
    
