<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show_profile()
    {

        try {
            $profile = auth()->user();
            $response = [
                'status' => 200,
                'message' => 'Ok',
                'data' => $profile->toArray(),
            ];
            return response()->json($response, $response['status']);
        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'message' => 'fail',
            ];
            return response()->json($response, $response['status']);
        }
    }

    public function update_password(Request $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            if (Hash::check($request->current_password, $user->password)) {
                if ($request->new_password == $request->confirm_password) {
                    $user->update([
                        'password' => Hash::make($request->new_password)
                    ]);
                    $response = [
                        'status' => 200,
                        'message' => 'Password updated successfully',
                    ];
                    return response()->json($response, $response['status']);
                } else {
                    $response = [
                        'status' => 400,
                        'message' => 'Confirm wrong password',
                    ];
                    return response()->json($response, $response['status']);
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'The old password is not suitable',
                ];
                return response()->json($response, $response['status']);
            }
        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'message' => 'fail',
            ];
            return response()->json($response, $response['status']);
        }
    }
}
