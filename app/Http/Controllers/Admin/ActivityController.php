<?php
/**
 * Created by PhpStorm.
 * User: Deelko 1
 * Date: 12/9/2021
 * Time: 3:24 PM
 */

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;


class ActivityController extends Controller
{

    public function index(Request $request)
    {

        if ($request->has('action')) {
            $action = $request->get('action');
            switch ($action) {
                case 'data':
                    return response()->json($this->handleData($request));
                    break;
                case 'current_data':
                    return response()->json($this->handleCurrentData($request));
                    break;
            }
        }

        $users = User::all();
        $activity_logs = Activity::orderBy('id','desc');
        $tables = Activity::groupBy('log_name')->pluck('log_name');

        $activity_logs = $activity_logs->get();

        return view('admin.pages.activity-log.activity-log',compact('users','tables','activity_logs'));
    }

    private function handleData(Request $request)
    {
        $this->validate($request, [
            'action'    => 'required|string',
            'user_id'   => 'sometimes|numeric',
            'log_event'  => 'sometimes|string',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date'   => 'sometimes|date_format:Y-m-d'
        ]);

        $data = Activity::with('causer')->orderBy('id', 'desc');
        if ($request->has('user_id')) {
            $data = $data->where('causer_id', request('user_id'));
        }
        if ($request->has('log_event')) {
            $data = $data->where('event', request('log_event'));
        }
        if ($request->has('from_date') && $request->has('to_date')) {
            $from = request('from_date') . " 00:00:00";
            $to = request('to_date') . " 23:59:59";
            $data = $data->whereBetween('created_at', [$from, $to]);
        }

        return $data->paginate(10);
    }

    private function handleCurrentData(Request $request)
    {
        $this->validate($request, [
            'log_id' => 'required|numeric'
        ]);

        $logId = request('log_id');
        $logHistory = Activity::find($logId)->with('causer');
        return ['log_history' => $logHistory];
    }

    public function handlePostRequest(Request $request)
    {
        if ($request->has('action')) {
            $action = $request->get('action');
            switch ($action) {
                case 'delete':
                    $dayLimit = config('activitylog.delete_records_older_than_days');
                    Activity::whereRaw('log_date < NOW() - INTERVAL ? DAY', [$dayLimit])->delete();
                    return ['success' => true, 'message' => "Successfully deleted log data older than $dayLimit days"];
                    break;
            }
        }
    }

}
