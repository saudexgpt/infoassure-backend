<?php

namespace App\Http\Controllers\ISMS;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\ISMS\AssignedTask;
use App\Models\ISMS\Clause;
use App\Models\ISMS\ModuleActivity;
use App\Models\ISMS\ModuleActivityTask;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //


    public function fetchAllTasks(Request $request)
    {
        // Logic to fetch module calendar data
        // This could involve querying the database for tasks, activities, etc.
        // and returning them in a format suitable for the calendar view.
        // Example:
        $tasks = ModuleActivityTask::get();
        return response()->json(compact('tasks'), 200);
    }
    public function fetchModuleTaskByClause(Request $request)
    {
        // Logic to fetch module calendar data
        // This could involve querying the database for tasks, activities, etc.
        // and returning them in a format suitable for the calendar view.
        // Example:
        $clause_tasks = Clause::with('activities.tasks')->get();
        return response()->json(compact('clause_tasks'), 200);
    }
    public function fetchClientAssignedTasks(Request $request)
    {
        $client = $this->getClient();
        // Logic to fetch module calendar data
        // This could involve querying the database for tasks, activities, etc.
        // and returning them in a format suitable for the calendar view.
        // Example:
        $clause_tasks = Clause::with([
            'activities.tasks',
            'activities.tasks.assignedTask' => function ($q) use ($client) {
                $q->where('client_id', $client->id);
            },
            'activities.tasks.assignedTask.assignee'
        ])->get()
            ->groupBy('category');
        return response()->json(compact('clause_tasks'), 200);
    }
    public function storeClauseActivities(Request $request)
    {
        $request->validate([
            'clause_id' => 'required|integer|exists:isms.clauses,id',
            'details' => 'required|array',
        ]);
        $clause_id = $request->clause_id;
        $details = json_decode(json_encode($request->details));

        foreach ($details as $detail) {
            ModuleActivity::updateOrCreate([
                'clause_id' => $clause_id,
                'activity_no' => $detail->activity_no,
                'name' => $detail->name,
            ], [
                'description' => $detail->description
            ]);
        }
        return $this->fetchModuleTaskByClause($request);
    }
    public function updateClauseActivity(Request $request, ModuleActivity $moduleActivity)
    {
        $moduleActivity->update([
            'activity_no' => $request->activity_no,
            'name' => $request->name,
            'description' => $request->description
        ]);
        return $this->fetchModuleTaskByClause($request);
    }
    public function storeClauseActivityTasks(Request $request)
    {
        $request->validate([
            'clause_id' => 'required|integer|exists:isms.clauses,id',
            'module_activity_id' => 'required|integer|exists:isms.module_activities,id',
            'details' => 'required|array',
        ]);
        $clause_id = $request->clause_id;
        $module_activity_id = $request->module_activity_id;
        $details = json_decode(json_encode($request->details));

        foreach ($details as $detail) {
            ModuleActivityTask::updateOrCreate([
                'clause_id' => $clause_id,
                'module_activity_id' => $module_activity_id,
                'name' => $detail->name,
            ], [
                // 'description' => $detail->description,
                'dependency' => $detail->dependency,
                'hint' => $detail->hint,
                'document_template_ids' => $detail->document_template_ids,
                // 'priority' => $detail->priority,
                // 'occurence' => $detail->occurence
            ]);
        }
        return $this->fetchModuleTaskByClause($request);
    }
    public function updateClauseActivityTask(Request $request, ModuleActivityTask $moduleActivityTask)
    {
        $moduleActivityTask->update([
            'document_template_ids' => $request->document_template_ids,
            'name' => $request->name,
            'description' => $request->description,
            'dependency' => $request->dependency,
            'hint' => $request->hint,
            'priority' => $request->priority,
            'occurence' => $request->occurence
        ]);
        return $this->fetchModuleTaskByClause($request);
    }

    public function assignTaskToUser(Request $request)
    {
        $request->validate([
            'module_activity_task_id' => 'required|integer|exists:isms.module_activity_tasks,id',
            'assignee_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        $module_activity_task_id = $request->module_activity_task_id;
        $assignee_id = $request->assignee_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $client_id = $this->getClient()->id;
        $user = $this->getUser();

        $task = ModuleActivityTask::find($module_activity_task_id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $assignedTask = $task->assignedTask()
            ->updateOrCreate(
                [
                    'module_activity_task_id' => $module_activity_task_id,
                    'client_id' => $client_id,
                    'clause_id' => $task->clause_id,
                    'module_activity_id' => $task->module_activity_id
                ],
                [
                    'assignee_id' => $assignee_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'assigned_by' => $user->id,
                ]
            );
        // send notification to the assignee
        $title = "Task Assigned";
        $description = "$user->name assigned you a task on the ISMS module.";
        $this->sendNotification($title, $description, [$assignee_id]);

        $assignedTask = $assignedTask->with('assignee')->find($assignedTask->id);
        return response()->json(['message' => 'Task assigned successfully', 'assigned_task' => $assignedTask], 200);
    }
    public function fetchProjectCalendarData(Request $request)
    {
        $client = $this->getClient();
        $user = $this->getUser();
        $tasks = AssignedTask::where('client_id', $client->id)
            ->with(['clause', 'activity', 'task'])
            ->get();
        // ->groupBy('clause_id');
        $count_clause_tasks = AssignedTask::where('client_id', $client->id)
            ->groupBy('clause_id')
            ->select('clause_id', \DB::raw("COUNT(CASE WHEN status = 'completed' THEN assigned_tasks.id END ) / COUNT(*) as progress"))
            ->get()
            ->groupBy('clause_id');

        $count_activity_tasks = AssignedTask::where('client_id', $client->id)
            ->groupBy('module_activity_id')
            ->select('module_activity_id', \DB::raw("COUNT(CASE WHEN status = 'completed' THEN assigned_tasks.id END ) / COUNT(*) as progress"))
            ->get()
            ->groupBy('module_activity_id');
        // return response()->json(compact('count_clause_tasks', 'count_activity_tasks'), 200);
        $data = [];
        foreach ($tasks as $task) {
            $clause_id = $task->clause_id;
            $activity_id = $task->module_activity_id;
            // $task_id = $task->module_activity_task_id;
            $data[] = [
                'id' => 'control_' . $clause_id,
                'name' => $task->clause->name,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_clause_tasks[$clause_id][0]->progress
                ],
            ];
            $data[] = [
                'id' => 'activity_' . $activity_id,
                'name' => $task->activity->name,
                'parent' => 'control_' . $task->clause->id,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_activity_tasks[$activity_id][0]->progress
                ],
            ];
            $data[] = [
                'uid' => $task->id,
                'id' => 'task_' . $task->id,
                'name' => $task->task->name,
                'parent' => 'activity_' . $activity_id,
                'start' => strtotime($task->start_date) * 1000, // Convert to milliseconds
                'end' => strtotime($task->end_date) * 1000,
                'start_date' => $task->start_date, // Convert to milliseconds
                'end_date' => $task->end_date,
                'owner' => $task->assignee ? $task->assignee->name : 'Unassigned',
                'dependency' => 'task_' . $task->task->dependency,
                'completed' => [
                    'amount' => $task->progress
                ],
                'progress' => $task->progress,
                'status' => $task->status,
                'action' => 'Click to view',
            ];
        }
        $uniqueData = array_values(array_unique($data, SORT_REGULAR));

        return response()->json(['data' => $uniqueData], 200);
    }
    public function fetchMyCalendarData(Request $request)
    {
        $client = $this->getClient();
        $user = $this->getUser();
        $tasks = AssignedTask::where('assignee_id', $user->id)
            ->where('client_id', $client->id)
            ->with(['clause', 'activity', 'task'])
            ->get();
        // ->groupBy('clause_id');
        $count_clause_tasks = AssignedTask::where('assignee_id', $user->id)
            ->where('client_id', $client->id)
            ->groupBy('clause_id')
            ->select('clause_id', \DB::raw('COUNT(CASE WHEN progress = 1 THEN assigned_tasks.id END ) / COUNT(*) as progress'))
            ->get()
            ->groupBy('clause_id');

        $count_activity_tasks = AssignedTask::where('assignee_id', $user->id)
            ->where('client_id', $client->id)
            ->groupBy('module_activity_id')
            ->select('module_activity_id', \DB::raw('COUNT(CASE WHEN progress = 1 THEN assigned_tasks.id END ) / COUNT(*) as progress'))
            ->get()
            ->groupBy('module_activity_id');
        // return response()->json(compact('count_clause_tasks', 'count_activity_tasks'), 200);
        $data = [];
        foreach ($tasks as $task) {
            $clause_id = $task->clause_id;
            $activity_id = $task->module_activity_id;
            // $task_id = $task->module_activity_task_id;
            $data[] = [
                'id' => 'control_' . $clause_id,
                'name' => $task->clause->name,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_clause_tasks[$clause_id][0]->progress
                ],
            ];
            $data[] = [
                'id' => 'activity_' . $activity_id,
                'name' => $task->activity->name,
                'parent' => 'control_' . $task->clause->id,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_activity_tasks[$activity_id][0]->progress
                ],
            ];
            $data[] = [
                'uid' => $task->id,
                'id' => 'task_' . $task->id,
                'name' => 'Task-' . $task->id . ': ' . $task->task->name,
                'parent' => 'activity_' . $activity_id,
                'start' => strtotime($task->start_date) * 1000, // Convert to milliseconds
                'end' => strtotime($task->end_date) * 1000,
                'start_date' => $task->start_date, // Convert to milliseconds
                'end_date' => $task->end_date,
                'owner' => $task->assignee ? $task->assignee->name : 'Unassigned',
                'dependency' => 'task_' . $task->task->dependency,
                'completed' => [
                    'amount' => $task->progress
                ],
                'progress' => $task->progress,
                'status' => $task->status,
                'action' => 'Click to view',
            ];
        }
        $uniqueData = array_values(array_unique($data, SORT_REGULAR));

        return response()->json(['data' => $uniqueData], 200);
    }

    public function markTaskAsDone(Request $request, AssignedTask $task)
    {
        // $user = $this->getUser();
        $task->update(['status' => 'submitted', 'progress' => 1]);
        // Optionally, you can return the updated task or a success message
        return response()->json(['message' => 'Task marked as done successfully', 'task' => $task], 200);
    }

    public function markTaskAsCompleted(Request $request, AssignedTask $task)
    {
        // $user = $this->getUser();
        $task->update(['status' => 'completed']);
        // Optionally, you can return the updated task or a success message
        $assignedTask = $task->with('assignee')->find($task->id);
        return response()->json(['message' => 'Task marked as done successfully', 'task' => $assignedTask], 200);
    }


}