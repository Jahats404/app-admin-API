<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    public function get_noHp(Request $request)
    {
        $input = $request->all();
        $user = auth()->user()->email;
        // dd($user);
        $validator = Validator::make($input, [
            'email' => 'required|email|in:' . $user,
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 404,
                'message' => 'email tidak sama',
            ];
        } else {
            $check_user = User::where('email', '=', $input['email'])->first();

            if ($check_user) {
                $response = [
                    'status' => 200,
                    'message' => 'Ok',
                    'data' => [
                        'email' => $check_user->email,
                        'no Hp' => $check_user->no_hp,
                    ],
                ];
            }
        }
        return response()->json($response, $response['status']);
    }

    public function update_nohp(Request $request)
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            $validator = Validator::make($request->all(), [
                'no_hp' => 'required|numeric|digits_between:10,13|unique:users,no_hp,' . $user->id,
            ]);
            if ($validator->fails()) {
                $response = [
                    'status' => 400,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ];
            } else {
                if ($user->no_hp === $request->no_hp) {
                    $response = [
                        'status' => 400,
                        'message' => 'No Hp sama dengan sebelumnya',
                    ];
                } else {
                    $user->update([
                        'no_hp' => $request->no_hp
                    ]);
                    $response = [
                        'status' => 200,
                        'message' => 'No Hp berhasil diupdate',
                    ];
                }
            }
            return response()->json($response, $response['status']);
        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'message' => 'fail',
            ];
            return response()->json($response, $response['status']);
        }
    }
}
