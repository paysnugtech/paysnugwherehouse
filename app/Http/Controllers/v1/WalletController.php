<?php
namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function allWallet(Request $request)
    {
        $dept_id = $request->attributes->get('dept_id');

        $wallet = Wallet::where('dept_id', $dept_id)
        ->get();
        return response()->json(['message' => 'Wallets Fetch successfully', 'data' => $wallet]);
    }
}
