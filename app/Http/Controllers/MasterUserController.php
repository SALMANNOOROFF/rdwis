<?php

namespace App\Http\Controllers;

use App\Models\CenAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterUserController extends Controller
{
    public function index()
    {
        $users = CenAccount::orderBy('acc_username')->get();
        return view('master.users', compact('users'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'acc_id' => 'required|integer|exists:pgsql.cen.accounts,acc_id',
        ]);

        $user = CenAccount::find($request->acc_id);
        if ($user) {
            // Reset to 12345 using the system's hash logic
            $defaultPassword = (string) config('auth.default_password', '12345');
            $user->acc_pass = CenAccount::hashPassword((string) $user->acc_username, $defaultPassword);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "Password for {$user->acc_username} has been reset to {$defaultPassword}."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }
}
