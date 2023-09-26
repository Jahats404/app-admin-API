<?php

namespace App\Http\Controllers;

use Countable;
use App\Models\User;
use App\Models\Logactivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required',
            'nama_lengkap' => 'required',
            'username' => 'required',
            'divisi' => 'required',
            'no_hp' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 401,
                'message' => 'Error',
                'error' => $validator->errors(),
            ], 401);
        }

        unset($input['confirm_password']);
        $input['password'] = Hash::make($input['password']);

        $query = User::create($input);

        $response['token'] = $query->createToken('users')->accessToken;
        $response['email'] = $query->email;

        return response()->json($response, 201);
    }

    public function login(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => 'Error',
                'error' => $validator->errors(),
            ], 401);
        }

        $attemptsKey = 'login_attemps_' . $input['username'];
        $attempts = Cache::get($attemptsKey, 0);

        if ($attempts >= 3) {
            $response = [
                'status' => 429,
                'pesan' => 'Anda telah mencapai batas kesalahan Login'
            ];

            return response()->json($response, 429);
        }

        $check_users = User::where('username', '=', $input['username'])->first();
        $userId = DB::table('users')
            ->select('id')
            ->where('username', '=', $input['username'])
            ->get();

        if ($check_users) {

            $password = $input['password'];

            if (Hash::check($password, $check_users['password'])) {

                $response['token'] = $check_users->createToken('users')->accessToken;
                $response['status'] = 200;
                $response['message'] = 'Berhasil Login';

                Cache::forget($attemptsKey);


                $log = new Logactivity();
                $log->user_id = $userId[0]->id;
                $log->activity = 'Login';
                $log->notes = 'Berhasil Login';
                $log->save();

                return response()->json($response, 200);
            } else {

                $response = [
                    'status' => 401,
                    'pesan' => 'Password Salah'
                ];

                Cache::put($attemptsKey, $attempts + 1, Carbon::now()->addMinutes(1));

                return response()->json($response, 401);
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Gagal Login';

            Cache::put($attemptsKey, $attempts + 1, Carbon::now()->addMinutes(1));

            return response()->json($response, 401);
        }
    }

    public function logout(Request $request)
    {

        $request->user()->token()->delete();
        return response()->json([
            'pesan' => 'User Berhasil Logout'
        ], 200);
    }

    public function getUser()
    {
        $query = User::all();


        // $response['email'] = $query->email;

        $response = [
            'status' => 200,
            'pesan' => 'Ok',
            'data' => $query->toArray(),
        ];

        return response()->json($response, $response['status']);
    }
}
