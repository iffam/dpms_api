<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PermitController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);  // `page` parameter
        $size = $request->query('size', 10);

        Paginator::currentPageResolver(fn() => $page);

        $permit = Permit::query()
            ->orderBy('created_at', 'desc')
            ->paginate($size);

        $permits = [
            'data' => $permit->items(),
            'pagination' => [
                'totalResults' => $permit->total(),
                'resultsPerPage' => $permit->perPage(),
                'currentPage' => $permit->currentPage(),
                'lastPage' => $permit->lastPage(),
                'startIndex' => ($permit->currentPage() - 1) * $permit->perPage(),
                'endIndex' => min($permit->currentPage() * $permit->perPage() - 1, $permit->total())
            ]
        ];

        return response()->json($permits, 200);
    }

    public function myPermit()
    {
        $user = auth()->user();

        if ($user && $permit = $user->permit) {
            $zones = $permit->zones->pluck('code');
            $qr = QrCode::format('png')->size(200)->generate($zones);
            $qrBase64 = base64_encode($qr);

            $response = [
                'permit_no' => $permit->id,
                'name' => $user->name,
                'employee_number' => $user->employee_number,
                'zones' => $zones,
                'qrCode' => 'data:image/png;base64,' . $qrBase64
            ];


            return response()->json($response, 200);
        }
        return response()->json(['message' => 'No permit found'], 404);
    }
}
