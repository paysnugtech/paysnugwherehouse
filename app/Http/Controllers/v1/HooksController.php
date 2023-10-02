<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hooks;
use App\Models\Wallet;

class HooksController extends Controller
{
    public function hooksData($walletId, Request $request)
    {
        $validatedData = $request->validate([
            'notificationUrl' => 'required|url|max:255',
            'reversalUrl' => 'required|url',
            'settlementUrl' => 'required|url|max:255',
        ]);
        $data = $request->all();
    
        // Find the wallet by ID
        $dept_id = $request->attributes->get('dept_id');
        $wallet = Wallet::where('dept_id', $dept_id)
            ->where('id', $walletId)
            ->where('status', 1)
            ->first();
    
        if (!$wallet) {
            return response()->json(['error' => 'Wallet Not Found'], 400);
        }
    
        // Find the hooks for the wallet
        $hooks = Hooks::where('wallet_id', $walletId)->first();
        try {
            if (!$hooks) {
            // If hooks don't exist, create a new record
            Hooks::create([
                'dept_id' => $wallet->dept_id,
                'wallet_id' => $wallet->id, // Use $wallet->id to get the wallet's ID
                'notification' => $data['notificationUrl'],
                'reversal' => $data['reversalUrl'],
                'settlement' => $data['settlementUrl'],
            ]);
            return response()->json(['message' => 'Hooks URL inserted']);
        }
    
        // If hooks exist, update the existing record
        $hooks->update([
            'dept_id' => $wallet->dept_id,
            'wallet_id' => $wallet->id, // Use $wallet->id to get the wallet's ID
            'notification' => $data['notificationUrl'],
            'reversal' => $data['reversalUrl'],
            'settlement' => $data['settlementUrl'],
        ]);
    
        return response()->json(['message' => 'Hooks URL updated']);
        } catch (\Exception $e) {
            // Handle the exception, and log or return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
       
    }
    
    


}
