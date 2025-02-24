<?php

namespace App\Http\Controllers;

use App\Models\PermitRequestApplication;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;

class PermitRequestApplicationController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);  // `page` parameter
        $size = $request->query('size', 10);

        Paginator::currentPageResolver(fn() => $page);

        $usr = PermitRequestApplication::query()
            ->with('user')
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
}
