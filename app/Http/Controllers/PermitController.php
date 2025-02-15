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

            $qr_data = [
                'permit_no' => $permit->id,
                'name' => $user->name,
                'employee_number' => $user->employee_number,
                'zones' => $zones
            ];

            $qr = QrCode::format('png')->size(200)->generate(json_encode($qr_data));
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


    public function validate(Request $request)
    {
        $request->validate([
            'permit_no' => 'required|exists:permits,id',
            'zones' => 'required|array',
            'zones.*' => 'string|exists:zones,code'
        ]);

        $permit = Permit::find($request->permit_no);

        $validZones = $permit->zones->pluck('code')->toArray();
        $invalidZones = array_diff($request->zones, $validZones);

        if (!empty($invalidZones)) {
            return response()->json(['message' => 'Invalid zones: ' . implode(', ', $invalidZones)], 400);
        }

        foreach ($request->zones as $zone) {
            if (!$permit->zones->contains('code', $zone)) {
            return response()->json(['message' => 'Invalid zone: ' . $zone], 400);
            }
        }

        return response()->json(['message' => 'Valid permit'], 200);

        return response()->json(['message' => 'Invalid permit'], 200);
    }
}
