<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use App\Models\Department;
use App\Models\DeptToken;

class DepartmentTokenController extends Controller
{
    public function generateAndStoreTokens($departmentId)
    {
        $department = Department::find($departmentId);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $deptToken = DeptToken::where('dept_id', $departmentId)->first();
         $publicKey = 'psgpk-'.bin2hex(random_bytes(16)); // Generate public key
         $secretKey = 'psgsk-'.bin2hex(random_bytes(16)); // Generate secret key
         $encryptedPublicKey =  hash('sha512', trim($publicKey));
         $encryptedSecretKey =  hash('sha512', trim($secretKey));
        

        if ($deptToken) {
            // Tokens already exist, update them
            $deptToken->update([
                'public_key' => $encryptedPublicKey,
                'secret_key' => $encryptedSecretKey,
            ]);
        } else {
            DeptToken::create([
                'dept_id' => $departmentId,
                'public_key' => $encryptedPublicKey,
                'secret_key' => $encryptedSecretKey,
            ]);
        }

        return response()->json(['message' => 'Tokens generated','public_key'=>$publicKey,'secret_key'=>$secretKey]);
    }

    public function getTokensForDepartment($departmentId)
    {
        $deptToken = DeptToken::where('dept_id', $departmentId)->first();

        if (!$deptToken) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $encryptedPublicKey = $deptToken->public_key;
        $encryptedSecretKey = $deptToken->secret_key;

        $publicKey = Crypt::decrypt($encryptedPublicKey);
        $secretKey = Crypt::decrypt($encryptedSecretKey);

        return response()->json([
            'public_key' => $publicKey,
            'secret_key' => $secretKey,
        ]);
    }
}
