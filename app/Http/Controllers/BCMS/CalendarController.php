<?php

namespace App\Http\Controllers\BCMS;

use App\Http\Controllers\Controller;
use App\Models\BCMS\TaskLog;
use App\Models\DocumentTemplate;
use App\Models\BCMS\AssignedTask;
use App\Models\BCMS\AssignedTaskComment;
use App\Models\BCMS\Clause;
use App\Models\BCMS\ModuleActivity;
use App\Models\BCMS\ModuleActivityTask;
use App\Models\Project;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //
    public function __construct(Request $httpRequest)
    {
        parent::__construct($httpRequest);
        $this->middleware(function ($request, $next) {

            $this->autoGenerateAndSaveActivityTasks();
            return $next($request);
        });


    }
    private function autoGenerateAndSaveActivityTasks()
    {
        $activities = $this->generateActivities(); // $request->names;
        foreach ($activities as $activity) {
            $clause = Clause::where('name', $activity->clause)->first();
            $process = $activity->process;
            $activity_no = $activity->activity_no;
            $description = $activity->description;
            $occurence = $activity->occurence;
            $implementation_guide = $activity->implementation_guide;
            $tasks = $activity->tasks;
            $evidences = $activity->evidences;
            // $document_template_ids = $this->createDocumentTemplate($evidences);
            ModuleActivityTask::firstOrCreate([
                'clause_id' => $clause->id,
                'name' => $process,
            ], [
                'activity_no' => $activity_no,
                'description' => $description,
                'implementation_guide' => $implementation_guide,
                // 'document_template_ids' => $document_template_ids,
                'tasks' => $tasks,
                'occurence' => $occurence
            ]);
        }


    }
    private function createDocumentTemplate($titles)
    {
        $template_ids = [];
        foreach ($titles as $title) {
            $title = ucwords(trim($title));
            $template = DocumentTemplate::firstOrCreate(['title' => $title, 'first_letter' => substr($title, 0, 1)]);
            $template_ids[] = $template->id;
        }
        return $template_ids;

    }
    private function generateActivities()
    {
        //
        // $message = "As an BCMS manager list all possible 'ASSET TYPES' a company can have. ";
        // $instruction = "Provide the response in a string array format";

        // $content = $message . $instruction;

        // $result = OpenAI::chat()->create([
        //     'model' => 'gpt-3.5-turbo',
        //     'messages' => [
        //         ['role' => 'user', 'content' => $content],
        //     ],
        // ]);

        // // response is score and justification
        // $ai_response = json_decode($result->choices[0]->message->content);
        // return $ai_response;
        $filename = portalPulicPath('bcms_activities.json');
        $file_content = file_get_contents($filename);
        return json_decode($file_content);
        // print_r($result);
    }

    public function fetchAllTasks(Request $request)
    {
        // Logic to fetch module calendar data
        // This could involve querying the database for tasks, activities, etc.
        // and returning them in a format suitable for the calendar view.
        // Example:
        $tasks = ModuleActivityTask::orderBy('clause_id')->get();
        return response()->json(compact('tasks'), 200);
    }
    public function showTask(Request $request, ModuleActivityTask $task)
    {
        $client = $this->getClient();
        $task = $task->with([
            'assignedTask' => function ($q) use ($client) {
                $q->where('client_id', $client->id);
            },
            'assignedTask.assignee',
        ])->find($task->id);
        return response()->json(compact('task'), 200);
    }

    public function fetchTaskLogs(Request $request)
    {
        $client = $this->getClient();
        $today_date = date('Y-m-d', strtotime('now'));
        $task_logs = TaskLog::where(['client_id' => $client->id, 'assigned_task_id' => $request->assigned_task_id])->get();
        return response()->json(compact('task_logs', 'today_date'), 200);
    }

    public function fetchModuleTaskByClause(Request $request)
    {
        // Logic to fetch module calendar data
        // This could involve querying the database for tasks, activities, etc.
        // and returning them in a format suitable for the calendar view.
        // Example:
        $clause_tasks = Clause::with('tasks')->get();
        return response()->json(compact('clause_tasks'), 200);
    }
    public function fetchClientAssignedTasksNew(Request $request)
    {
        $client = $this->getClient();
        $user = $this->getUser();
        if ($user->login_as === 'client') {
            $clause_tasks = ModuleActivityTask::join('assigned_tasks', 'module_activity_tasks.id', 'assigned_tasks.module_activity_task_id')
                ->with([
                    'clause',
                    'assignedTask' => function ($q) use ($client, $user) {
                        $q->where('client_id', $client->id);
                        $q->where('assignee_id', $user->id);
                    },
                    'assignedTask.assignee'
                ])
                ->where('assigned_tasks.client_id', $client->id)
                ->where('assigned_tasks.assignee_id', $user->id)

                ->orderBy('id')
                ->orderBy('module_activity_tasks.clause_id')
                ->select('module_activity_tasks.*')
                ->get()
                ->groupBy('occurence');
        } else {
            $clause_tasks = ModuleActivityTask::with([
                'clause',
                'assignedTask' => function ($q) use ($client) {
                    $q->where('client_id', $client->id);
                },
                'assignedTask.assignee'
            ])
                ->orderBy('id')
                ->orderBy('module_activity_tasks.clause_id')
                ->get()
                ->groupBy('occurence');
        }
        return response()->json(compact('clause_tasks'), 200);
    }
    public function fetchClientAssignedTasks(Request $request)
    {
        $client = $this->getClient();
        $user = $this->getUser();
        if ($user->login_as === 'client') {
            $clause_tasks = Clause::join('assigned_tasks', 'clauses.id', 'assigned_tasks.clause_id')
                ->join('module_activity_tasks', 'module_activity_tasks.id', 'assigned_tasks.module_activity_task_id')
                ->with([
                    'tasks',
                    'tasks.assignedTask' => function ($q) use ($client, $user) {
                        $q->where('client_id', $client->id);
                        $q->where('assignee_id', $user->id);
                    },
                    'tasks.assignedTask.assignee'
                ])
                ->where('assigned_tasks.client_id', $client->id)
                ->where('assigned_tasks.assignee_id', $user->id)
                ->select('clauses.*')
                ->get()
                ->groupBy('category');
        } else {
            $clause_tasks = Clause::with([
                'tasks',
                'tasks.assignedTask' => function ($q) use ($client) {
                    $q->where('client_id', $client->id);
                },
                'tasks.assignedTask.assignee'
            ])->get()
                ->groupBy('category');
        }

        return response()->json(compact('clause_tasks'), 200);
    }
    public function storeClauseActivityTasks(Request $request)
    {
        $request->validate([
            'clause_id' => 'required|integer|exists:isms.clauses,id',
            'details' => 'required|array',
        ]);
        $clause_id = $request->clause_id;
        $details = json_decode(json_encode($request->details));
        foreach ($details as $detail) {
            ModuleActivityTask::updateOrCreate([
                'clause_id' => $clause_id,
                'name' => $detail->name,
            ], [
                'activity_no' => $detail->activity_no,
                'description' => $detail->description,
                'implementation_guide' => $detail->implementation_guide,
                'tasks' => $detail->tasks,
                // 'dependency' => $detail->dependency,
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
            'activity_no' => $request->activity_no,
            'dependency' => $request->dependency,
            'implementation_guide' => $request->implementation_guide,
            'priority' => $request->priority,
            'occurence' => $request->occurence
        ]);
        return $this->fetchModuleTaskByClause($request);
    }

    public function assignTaskToUser(Request $request)
    {
        $year = date('Y', strtotime('now'));
        $request->validate([
            'module_activity_task_id' => 'required|integer|exists:isms.module_activity_tasks,id',
            'assignee_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'recurrence' => 'required|string'
        ]);
        $module_activity_task_id = $request->module_activity_task_id;
        $assignee_id = $request->assignee_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $recurrence = $request->recurrence;
        $client_id = $this->getClient()->id;
        $user = $this->getUser();

        $task = ModuleActivityTask::find($module_activity_task_id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $project = Project::where(['client_id' => $client_id, 'year' => $year])
            ->where('title', 'LIKE', '%NDPA%')
            ->first();

        if ($project) {
            $assignedTask = $task->assignedTask()
                ->updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'module_activity_task_id' => $module_activity_task_id,
                        'client_id' => $client_id,
                        'clause_id' => $task->clause_id,
                    ],
                    [
                        'assignee_id' => $assignee_id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'assigned_by' => $user->id,
                        'recurrence' => $recurrence
                    ]
                );
            // Create task logs for the specified recurrence
            // $recurrence = $task->occurence;
            $this->setupTaskLogForRecurrentTasks($assignedTask->id, $recurrence, $project, $start_date, $end_date);
            // send notification to the assignee
            $title = "Task Assigned";
            $description = "$user->name assigned you a task on the NDPA module.";
            $this->sendNotification($title, $description, [$assignee_id]);

            $assignedTask = $assignedTask->with('assignee')->find($assignedTask->id);
            return response()->json(['message' => 'Task assigned successfully', 'assigned_task' => $assignedTask], 200);
        }
        return response()->json(['message' => "You need to subscribe to the NDPA module for $year"], 403);
    }

    private function setupTaskLogForRecurrentTasks($assignedTaskId, $recurrence, $project, $start_date, $end_date)
    {
        $client_id = $this->getClient()->id;
        switch ($recurrence) {
            case 'Weekly':
                for ($i = 1; $i <= 52; $i++) {
                    $recurrence_tag = "Week $i";
                    TaskLog::firstOrCreate([
                        'client_id' => $client_id,
                        'assigned_task_id' => $assignedTaskId,
                        'start_date' => $start_date,
                    ], [
                        'recurrence_tag' => $recurrence_tag,
                        'deadline' => $end_date
                    ]);

                    $start_date = date('Y-m-d', strtotime("+$i week", strtotime($start_date)));
                    $end_date = date('Y-m-d', strtotime("+$i week", strtotime($end_date)));
                }
                break;
            case 'Monthly':
                for ($i = 1; $i <= 12; $i++) {
                    $recurrence_tag = "Month $i";
                    TaskLog::firstOrCreate([
                        'client_id' => $client_id,
                        'assigned_task_id' => $assignedTaskId,
                        'start_date' => $start_date,
                    ], [
                        'recurrence_tag' => $recurrence_tag,
                        'deadline' => $end_date
                    ]);

                    $start_date = date('Y-m-d', strtotime("+$i month", strtotime($start_date)));
                    $end_date = date('Y-m-d', strtotime("+$i month", strtotime($end_date)));
                }
                break;

            case 'Quarterly':
                for ($i = 1; $i <= 4; $i++) {
                    $recurrence_tag = "Q $i";
                    TaskLog::firstOrCreate([
                        'client_id' => $client_id,
                        'assigned_task_id' => $assignedTaskId,
                        'start_date' => $start_date,
                    ], [
                        'recurrence_tag' => $recurrence_tag,
                        'deadline' => date('Y-m-d', strtotime("+3 months", strtotime($start_date)))
                    ]);

                    $start_date = date('Y-m-d', strtotime("+3 months", strtotime($start_date)));
                    $start_date = date('Y-m-d', strtotime("+3 months", strtotime($start_date)));
                }
                break;
            case 'Biannually':
                $start_date = $project->start_date;
                for ($i = 1; $i <= 2; $i++) {
                    $recurrence_tag = "H $i";
                    TaskLog::firstOrCreate([
                        'client_id' => $client_id,
                        'assigned_task_id' => $assignedTaskId,
                        'start_date' => $start_date,
                    ], [
                        'recurrence_tag' => $recurrence_tag,
                        'deadline' => date('Y-m-d', strtotime("+6 months", strtotime($start_date)))
                    ]);

                    $start_date = date('Y-m-d', strtotime("+6 months", strtotime($start_date)));
                }
                break;
            case 'Annually':
                $start_date = $project->start_date;
                $recurrence_tag = "Annual";
                TaskLog::firstOrCreate([
                    'client_id' => $client_id,
                    'assigned_task_id' => $assignedTaskId,
                    'start_date' => $start_date,
                ], [
                    'recurrence_tag' => $recurrence_tag,
                    'deadline' => date('Y-m-d', strtotime("+1 year", strtotime($start_date)))
                ]);

                break;

            default:
                $start_date = $project->start_date;
                $recurrence_tag = $recurrence;
                TaskLog::firstOrCreate([
                    'client_id' => $client_id,
                    'assigned_task_id' => $assignedTaskId,
                    'start_date' => $start_date,
                ], [
                    'recurrence_tag' => $recurrence_tag,
                    'deadline' => date('Y-m-d', strtotime("+1 year", strtotime($start_date)))
                ]);

                break;
        }
    }
    public function fetchProjectCalendarData(Request $request)
    {
        $client = $this->getClient();
        $user = $this->getUser();
        $tasks = AssignedTask::where('client_id', $client->id)
            ->with(['clause', 'task'])
            ->get();
        // ->groupBy('clause_id');
        $count_clause_tasks = AssignedTask::where('client_id', $client->id)
            ->groupBy('clause_id')
            ->select('clause_id', \DB::raw("COUNT(CASE WHEN status = 'completed' THEN assigned_tasks.id END ) / COUNT(*) as progress"))
            ->get()
            ->groupBy('clause_id');

        $count_activity_tasks = AssignedTask::where('client_id', $client->id)
            ->groupBy('module_activity_task_id')
            ->select('module_activity_task_id', \DB::raw("COUNT(CASE WHEN status = 'completed' THEN assigned_tasks.id END ) / COUNT(*) as progress"))
            ->get()
            ->groupBy('module_activity_task_id');
        // return response()->json(compact('count_clause_tasks', 'count_activity_tasks'), 200);
        $data = [];
        foreach ($tasks as $task) {
            $clause_id = $task->clause_id;
            $module_activity_task_id = $task->module_activity_task_id;
            // $task_id = $task->module_activity_task_id;
            $data[] = [
                'id' => 'control_' . $clause_id,
                'name' => $task->clause->name,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_clause_tasks[$clause_id][0]->progress
                ],
            ];
            // $data[] = [
            //     'id' => 'activity_' . $module_activity_task_id,
            //     'name' => $task->activity->name,
            //     'parent' => 'control_' . $task->clause->id,
            //     'owner' => '',
            //     'completed' => [
            //         'amount' => (float) $count_activity_tasks[$module_activity_task_id][0]->progress
            //     ],
            // ];
            $data[] = [
                'uid' => $task->id,
                'id' => 'task_' . $task->id,
                'name' => $task->task->name,
                'parent' => 'control_' . $clause_id,
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
            ->groupBy('module_activity_task_id')
            ->select('module_activity_task_id', \DB::raw('COUNT(CASE WHEN progress = 1 THEN assigned_tasks.id END ) / COUNT(*) as progress'))
            ->get()
            ->groupBy('module_activity_task_id');
        // return response()->json(compact('count_clause_tasks', 'count_activity_tasks'), 200);
        $data = [];
        foreach ($tasks as $task) {
            $clause_id = $task->clause_id;
            $module_activity_task_id = $task->module_activity_task_id;
            // $task_id = $task->module_activity_task_id;
            $data[] = [
                'id' => 'control_' . $clause_id,
                'name' => $task->clause->name,
                'owner' => '',
                'completed' => [
                    'amount' => (float) $count_clause_tasks[$clause_id][0]->progress
                ],
            ];
            // $data[] = [
            //     'id' => 'activity_' . $activity_id,
            //     'name' => $task->activity->name,
            //     'parent' => 'control_' . $task->clause->id,
            //     'owner' => '',
            //     'completed' => [
            //         'amount' => (float) $count_activity_tasks[$activity_id][0]->progress
            //     ],
            // ];
            $data[] = [
                'uid' => $task->id,
                'id' => 'task_' . $task->id,
                'name' => 'Task-' . $task->id . ': ' . $task->task->name,
                'parent' => 'control_' . $task->clause->id,
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

    public function markTaskAsCompleted(Request $request, TaskLog $taskLog)
    {
        // $user = $this->getUser();
        $taskLog->update(['status' => 'Completed']);
        return response()->json(['message' => 'Action successfull'], 200);
    }

    public function saveAssignedTaskNote(Request $request, TaskLog $taskLog)
    {
        $validate = $request->validate([
            'notes' => 'required|string',
        ]);
        $taskLog->update(['notes' => $validate['notes']]);
        // Optionally, you can return the updated task or a success message
        return response()->json(['message' => 'Action successfull'], 200);
    }


    public function fetchTaskComments(Request $request)
    {
        $task = AssignedTask::find($request->task_id);
        $comments = AssignedTaskComment::with('commenter')
            ->where([
                'assigned_task_id' => $task->id
            ])->paginate(10);
        return response()->json(compact('comments'), 200);
    }

    public function postTaskcomment(Request $request)
    {
        $user = $this->getUser();
        $client = $this->getClient();
        $comment = AssignedTaskComment::create([
            'client_id' => $client->id,
            'assigned_task_id' => $request->assigned_task_id,
            'comment' => $request->comment,
            'comment_by' => $user->id
        ]);
        $comment = $comment->with('commenter')->find($comment->id);
        return response()->json(compact('comment'), 200);
    }
    public function updateTaskComment(Request $request, AssignedTaskComment $comment)
    {
        $comment->comment = $request->comment;
        $comment->save();
        $comment = $comment->with('commenter')->find($comment->id);
        return response()->json(compact('comment'), 200);
    }
    public function deleteComment(Request $request, AssignedTaskComment $comment)
    {
        $comment->is_deleted = 1;
        $comment->save();
        return response()->json([], 200);
    }

}
