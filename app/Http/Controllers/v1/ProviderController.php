<?php
namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Providers;

class ProviderController extends Controller
{

    public function createProvider(Request $request)
    {
        $validatedData = $request->validate([
            'provider_name' => 'required|string|max:255',
            'provider_type' => 'required|string',
        ]);

        $provider = Providers::create($validatedData);

        return response()->json(['message' => 'Provider created successfully', 'data' => $provider]);
    }
    
    public function providerList(Request $request){
        $provider = Providers::where('status', 1)
        ->get();
        return response()->json(['message' => 'Provider Fetch successfully', 'data' => $provider]);
    }
}
