<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IpWhitelist;
use App\Models\Department;

class IpWhitelistController extends Controller
{
public function addToWhitelist($departmentId, Request $request)
{
    $department = Department::find($departmentId);

    if (!$department) {
        return response()->json(['error' => 'Department not found'], 404);
    }

    $ipAddress = $request->input('ip_address');

    $existingWhitelist = IpWhitelist::where('dept_id', $departmentId)
        ->where('ip_address', $ipAddress)
        ->first();

    if ($existingWhitelist) {
        return response()->json(['error' => 'IP address already exists in the whitelist'], 400);
    }


    // Validate and save the whitelisted IP address
    IpWhitelist::create([
        'dept_id' => $departmentId,
        'ip_address' => $ipAddress,
    ]);

    return response()->json(['message' => 'IP address added to whitelist.']);
}

public function removeFromWhitelist($departmentId, $whitelistId)
{
    $whitelist = IpWhitelist::where('dept_id', $departmentId)->find($whitelistId);

    if (!$whitelist) {
        return response()->json(['error' => 'IP address not found in whitelist'], 404);
    }

    $whitelist->delete();

    return response()->json(['message' => 'IP address removed from whitelist.']);
}

public function getWhitelistedIPs($departmentId)
{
    $whitelistedIPs = IpWhitelist::where('dept_id', $departmentId)->get();

    return response()->json($whitelistedIPs);
}
}
