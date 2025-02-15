<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);  // `page` parameter
        $size = $request->query('size', 10);

        Paginator::currentPageResolver(fn() => $page);

        $usr = User::query()
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
}
