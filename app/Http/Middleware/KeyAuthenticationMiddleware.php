<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\DeptToken; // Import the model
use App\Models\IpWhitelist; // Import the IP whitelist model
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class KeyAuthenticationMiddleware
{
    public function handle($request, Closure $next, $keyType)
    {
        $keyHeader = $keyType === 'public' ? 'X-Public-Key' : 'X-Secret-Key';
        $providedKey = $request->header($keyHeader);
        $deptId = $this->getDeptId($keyType, $providedKey);
        if (!$deptId) {
            return response()->json(['error' => 'Unauthorized:'.$keyHeader.' Invalid or expired token'], 401);
        }

        // Check IP whitelisting
        
        // if (!$this->isIPWhitelisted($deptId, $request->ip())) {
        //     return response()->json(['error' => "Unauthorized Ip"], 401);
        // }
        $request->attributes->set('dept_id', $deptId);

        return $next($request);
    }

 

    private function getDeptId($keyType, $providedKey)
    {
        $column = $keyType === 'public' ? 'public_key' : 'secret_key';
        $encryptedProvidedKey =  hash('sha512', trim($providedKey));

        $token = DeptToken::where("secret_key", $encryptedProvidedKey)
        ->where('status', true)
        ->first();
        if ($token) {
            return $token->dept_id;
        }

        return null;
    }

    private function isIPWhitelisted($deptId, $ip)
    {
        return IpWhitelist::where('dept_id', $deptId)
            ->where('ip_address', $ip)
            ->where('status', true)
            ->exists();
    }
}
