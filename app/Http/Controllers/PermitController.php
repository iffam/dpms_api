<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\PermitUsage;
use App\Models\Zone;
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
            ->with('usages.zone')
            ->with('user')
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
            $expire_alert = $permit->expired_at ? ($permit->expired_at->diffInDays(now()) < 7 ? round(abs($permit->expired_at->diffInDays(now()))) : null) : null;
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
                'expire_alert' => $expire_alert,
                'qrCode' => 'data:image/png;base64,' . $qrBase64
            ];


            return response()->json($response, 200);
        }
        return response()->json(['message' => 'No permit found'], 200);
    }


    public function validate(Request $request)
    {
        $request->validate([
            'checkpoint' => 'required|string',
            'permit_no' => 'required|exists:permits,id',
            'zones' => 'required|array',
            'zones.*' => 'string|exists:zones,code'
        ]);

        $permit = Permit::find($request->permit_no);
        $checkpoint = Zone::where('code', $request->checkpoint)->first();

        $validZones = $permit->zones->pluck('code')->toArray();
        $invalidZones = array_diff($request->zones, $validZones);

        if (!empty($invalidZones)) {
            return response()->json(['message' => 'Invalid zones: ' . implode(', ', $invalidZones)], 400);
        }

        foreach ($request->zones as $zone) {
            if (!$permit->zones->contains('code', $zone)) {
                return response()->json(['message' => 'Invalid zone: ' . $zone], 400);
            }
            PermitUsage::create([
                'permit_id' => $permit->id,
                'zone_id' => $checkpoint->id,
            ]);
        }

        return response()->json(['message' => 'Valid permit'], 200);

        return response()->json(['message' => 'Invalid permit'], 200);
    }
}
