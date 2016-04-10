<?php

namespace App\Models\Trello;

use Illuminate\Database\Eloquent\Model;

class Tasklist extends Model
{
    protected $connection = 'mysql_trello';

    public function tasks()
    {
        return $this->hasMany('App\Models\Trello\Task');
    }
}
