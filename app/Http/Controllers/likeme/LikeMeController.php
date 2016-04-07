<?php

namespace App\Http\Controllers\likeme;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Likeme\Like;

class LikeMeController extends Controller
{ 
    public function like($count)
    {
        $like = Like::find(1);
        $like->count = $like->count + $count;
        $like->save();

        return redirect()->route('like-me');
    }

    public function view()
    {
        $like = Like::find(1);
        return view('likeme.main',['count' => $like->count]);
    }
}
