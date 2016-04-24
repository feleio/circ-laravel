<?php

namespace App\Http\Controllers\trello;

use Illuminate\Http\Request;

use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Trello\Json;
use App\Models\Trello\Task;
use App\Models\Trello\Tasklist;

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

        $lists = Tasklist::orderBy('updated_at','desc')->whereHas('tasks', function($query){
            $query->where('isStage', true);
        })->get();

        $data = $this->getStageStat($lists, '');

        return view('trello.overview',$data);
    }

    public function overviewGet()
    {
        $lists = Tasklist::orderBy('updated_at','desc')->whereHas('tasks', function($query){
            $query->where('isStage', true);
        })->get();
        $data = $this->getStageStat($lists, '');

        return view('trello.overview',$data);
    }

    public function historyOverview()
    {
        $data = ['planDateDatas'=>$this->getTaskStat()];
        return view('trello.historyOverview',$data);
    }

    public function history($planDate)
    {
        $lists = Tasklist::whereHas('tasks', function($query)  use ($planDate){
            $query->where('isStage',false)->whereDate('plan_date', '=', $planDate);
        })->orderBy('updated_at','desc')->get();

        $data = $this->getStageStat($lists, $planDate);
        $data['isPlanDateFound'] = count($lists) > 0;
        $data['planDate'] = Carbon::createFromFormat('Y-m-d', $planDate);
        return view('trello.history',$data);
    }

    public function saveStage(Request $request){
        $planDate = $request->input('planDate');

        Task::where('isStage', true)->update([
            'isStage' => false,
            'plan_date' => $planDate]);
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
                $label = 'None';
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

    private function getStageStat($lists, $planDate)
    {
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

            if('' == $planDate){
                $tasks = $list->tasks()->where('isStage',true)->get();
            } else {
                $tasks = $list->tasks()->where('isStage',false)->whereDate('plan_date', '=', $planDate)->get();
            }
            foreach($tasks as $task){
                $label = $task->label;

                if($label == 'None'){
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

    private function getTaskStat(){
        return DB::connection('mysql_trello')->select("SELECT Date(t.plan_date) as dateonly, (select COALESCE(sum(plan_hour),0) from tasks t1 where Date(t1.plan_date) = dateonly and (t1.label = 'None' or t1.label = 'Done')) as plan, (select COALESCE(sum(plan_hour),0) from tasks t2 where Date(t2.plan_date) = dateonly and t2.label = 'Done') as done, (select COALESCE(sum(plan_hour),0) from tasks t3 where Date(t3.plan_date) = dateonly and t3.label = 'Delay') as delay, (select COALESCE(sum(plan_hour),0) from tasks t4 where Date(t4.plan_date) = dateonly and t4.label = 'Cancel') as cancel, (select COALESCE(sum(actual_hour),0) from tasks t5 where Date(t5.plan_date) = dateonly and t5.label = 'Done') as spent FROM tasks t where t.plan_date is not null and t.isStage = 0 group by dateonly order by dateonly desc");
    }
}
