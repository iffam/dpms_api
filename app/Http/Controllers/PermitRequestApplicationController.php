<?php

namespace App\Http\Controllers;

use App\Models\PermitRequestApplication;
use App\Models\Zone;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class PermitRequestApplicationController extends Controller
{
    public function index(Request $request)
    {

        if ($request->query('filter')) {
            $filter = $request->query('filter');
        }
        if ($filter === 'all') {
            $filter = null;
        }

        $page = $request->query('page', 1);  // `page` parameter
        $size = $request->query('size', 10);

        Paginator::currentPageResolver(fn() => $page);

        $usr = PermitRequestApplication::query()
            ->when($filter, function ($query, $filter) {
                return $query->where('status', $filter);
            })
            ->with('user')
            ->with('reviewedBy')
            ->orderBy('created_at', 'desc')
            ->paginate($size);

        $users = [
            'data' => $usr->items(),
            'pagination' => [
                'totalResults' => $usr->total(),
                'resultsPerPage' => $usr->perPage(),
                'currentPage' => $usr->currentPage(),
                'lastPage' => $usr->lastPage(),
                'startIndex' => ($usr->currentPage() - 1) * $usr->perPage(),
                'endIndex' => min($usr->currentPage() * $usr->perPage() - 1, $usr->total())
            ]
        ];

        return response()->json($users, 200);
    }

    public function myApplication(Request $request)
    {
        $page = $request->query('page', 1);  // `page` parameter
        $size = $request->query('size', 5);
        Paginator::currentPageResolver(fn() => $page);
        $user = auth()->user();
        $usr = $user->permitRequestApplications()->with('user')->orderBy('created_at', 'desc')->paginate($size);

        $application = [
            'data' => $usr->items(),
            'pagination' => [
                'totalResults' => $usr->total(),
                'resultsPerPage' => $usr->perPage(),
                'currentPage' => $usr->currentPage(),
                'lastPage' => $usr->lastPage(),
                'startIndex' => ($usr->currentPage() - 1) * $usr->perPage(),
                'endIndex' => min($usr->currentPage() * $usr->perPage() - 1, $usr->total())
            ]
        ];
        return response()->json($application, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'permit_type' => 'required',
            'zones' => 'required',
        ]);

        if ($request->permit_type === 'temporary') {
            $request->validate([
                'active_at' => 'required',
                'expired_at' => 'required',
            ]);
        }

        $user = auth()->user();
        $permitRequestApplication = new PermitRequestApplication();
        $permitRequestApplication->permit_type = $request->permit_type;
        $permitRequestApplication->active_at = $request->active_at
            ? Carbon::parse($request->active_at)->format('Y-m-d H:i:s')
            : null;
        $permitRequestApplication->expired_at = $request->expired_at
            ? Carbon::parse($request->expired_at)->format('Y-m-d H:i:s')
            : null;
        $permitRequestApplication->zones = $request->zones;
        $permitRequestApplication->user_id = $user->id;
        $permitRequestApplication->save();

        return response()->json($permitRequestApplication, 201);
    }

    public function review(Request $request,  PermitRequestApplication $permit_request_application)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'justification' => 'required',
        ]);

        $user = auth()->user();
        $permit_request_application->reviewed_by = $user->id;
        $permit_request_application->status = $request->status;
        $permit_request_application->justification = $request->justification;

        $permit_request_application->save();

        DB::beginTransaction();
        try {
            $permit_request_application->save();

            if ($request->status === 'approved') {
                $permit = $permit_request_application->permit()->create([
                    'user_id' => $permit_request_application->user_id,
                    'permit_request_application_id' => $permit_request_application->id,
                    'permit_type' => $permit_request_application->permit_type,
                    'active_at' => $permit_request_application->active_at,
                    'expired_at' => $permit_request_application->expired_at,
                ]);
                if ($permit_request_application->zones) {
                    foreach ($permit_request_application->zones as $z) {

                        $zone = Zone::where('code', $z)->first();
                        $permit->zones()->attach($zone);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to review permit request application'], 500);
        }


        return response()->json($permit_request_application, 200);
    }
}
