<?php

namespace App\Http\Controllers\trello;

use Illuminate\Http\Request;

use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Trello\Json;

class TaskController extends Controller
{
    public function overview(Request $request)
    {
        $jsonData = $request->input('data');

        // insert new json
        $json = new Json;
        $json->json = $jsonData;
        $json->save();

        // remove older jsons
        DB::connection('mysql_trello')->delete('delete from jsons where id not in ( select id from ( select id from jsons order by id desc limit 50)foo )');

        $data = $this->parseJsonData($jsonData);

        //var_dump($data['lists'][0]->cards[0]);
        return view('trello.overview', $data);
    }

        private function parseJsonData($jsonData)
    {
        $lists = json_decode($jsonData);

        $totalSpent = 0.0;
        $totalDone = 0.0;
        $totalPlan = 0.0;
        $totalDelay = 0.0;
        $totalCancel = 0.0;
    
        foreach($lists as $list){
            $totalListSpent = 0.0;
            $totalListDone = 0.0;
            $totalListPlan = 0.0;
            $totalListDelay = 0.0;
            $totalListCancel = 0.0;

            foreach($list->cards as $card){
                $label = '';
                if(count($card->labels) > 0){
                    $label = $card->labels[0];
                }
                $card->label = $label;

                if($label == ''){
                    $totalSpent += $card->actual;
                    $totalPlan += $card->plan;
                    $totalListSpent += $card->actual;
                    $totalListPlan += $card->plan;
                } else if($label == 'Done') {
                    $totalSpent += $card->actual;
                    $totalDone += $card->plan;
                    $totalPlan += $card->plan;
                    $totalListSpent += $card->actual;
                    $totalListDone += $card->plan;
                    $totalListPlan += $card->plan;
                } else if($label == 'Delay') {
                    $totalDelay += $card->plan;
                    $totalListDelay += $card->plan;
                } else if($label == 'Cancel') {
                    $totalCancel += $card->plan;
                    $totalListCancel += $card->plan;
                }
            }

            $list->totalListSpent = $totalListSpent;
            $list->totalListDone = $totalListDone;
            $list->totalListPlan = $totalListPlan;
            $list->totalListDelay = $totalListDelay;
            $list->totalListCancel = $totalListCancel;
        }

        return [
            'lists' => $lists, 
            'totalSpent' => $totalSpent, 
            'totalDone' => $totalDone, 
            'totalPlan' => $totalPlan, 
            'totalDelay' => $totalDelay, 
            'totalCancel' => $totalCancel
            ];
    }
}
