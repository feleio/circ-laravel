<?php

namespace App\Http\Controllers\trello;

use Illuminate\Http\Request;

use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Trello\Json;
use App\Models\Trello\Task;
use App\Models\Trello\TaskList;

use Carbon\Carbon;

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
        
        $this->saveStagingTasks($jsonData);
        $data = $this->parseJsonData();

        return view('trello.overview',$data);
    }

    public function overviewGet()
    {
        $data = $this->parseJsonData();

        return view('trello.overview',$data);
    }

    public function historyOverview()
    {
        $data = $this->parseJsonData();

        return view('trello.overview',$data);
    }

    public function history($planDate)
    {

        return view('trello.overview',$data);
    }

    public function saveStage(Request $request){
        $planDate = $request->input('planDate');
        $carbon = Carbon::createFromFormat('Y-m-d', $planDate);

        Task::where('isStage', true)->update([
            'isStage' => false,
            'plan_date' => $carbon]);
        return '';
    }

    private function saveStagingTasks($jsonData)
    {
        $lists = json_decode($jsonData);

        // delete old stage
        $tasks = Task::where('isStage',true)->delete();

        // save new stage task
        foreach($lists as $list){
            if(count($list->cards) > 0){
                // create list if not exist
                $tasklist = Tasklist::where('name', $list->name)->first();
                if($tasklist == null){
                    $tasklist = new Tasklist;
                    $tasklist->name = $list->name;
                    $tasklist->save();
                } else {
                    $tasklist->touch();
                }
            }

            foreach($list->cards as $card){
                $label = '';
                if(count($card->labels) > 0){
                    $label = $card->labels[0];
                }
                $card->label = $label;

                $task = new Task;
                $task->name = $card->task;
                $task->actual_hour = $card->actual;
                $task->plan_hour = $card->plan;
                $task->tasklist_id = $tasklist->id;
                $task->label = $label;
                $task->isStage = true;
                $task->save();
            }
        }
    }

    private function parseJsonData()
    {
        $lists = TaskList::orderBy('updated_at','desc')->whereHas('tasks', function($query){
            $query->where('isStage',true);
        })->get();

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

            $tasks = $list->tasks()->where('isStage',true)->get();
            foreach($tasks as $task){
                $label = $task->label;

                if($label == ''){
                    $totalSpent += $task->actual_hour;
                    $totalPlan += $task->plan_hour;
                    $totalListSpent += $task->actual_hour;
                    $totalListPlan += $task->plan_hour;
                } else if($label == 'Done') {
                    $totalSpent += $task->actual_hour;
                    $totalDone += $task->plan_hour;
                    $totalPlan += $task->plan_hour;
                    $totalListSpent += $task->actual_hour;
                    $totalListDone += $task->plan_hour;
                    $totalListPlan += $task->plan_hour;
                } else if($label == 'Delay') {
                    $totalDelay += $task->plan_hour;
                    $totalListDelay += $task->plan_hour;
                } else if($label == 'Cancel') {
                    $totalCancel += $task->plan_hour;
                    $totalListCancel += $task->plan_hour;
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
