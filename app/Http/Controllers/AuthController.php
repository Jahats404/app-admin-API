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
    public function register(Request $request){

        $input = $request->all();
        
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'level' => 'required'
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => 401,
                'message' => 'Error',
                'error' => $validator->errors(),
            ],401);
        }

        unset($input['confirm_password']);
        $input['password'] = Hash::make($input['password']);

        $query = User::create($input);

        $response['token'] = $query->createToken('users')->accessToken;
        $response['email'] = $query->email;
        $response['level'] = $query->level;

        return response()->json($response, 200);
    }

    public function login(Request $request){

        $input = $request->all();
        
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required'
        ]); 

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => 'Error',
                'error' => $validator->errors(),
            ],401);
        }

        $attemptsKey = 'login_attemps_' . $input['email'];
        $attempts = Cache::get($attemptsKey, 0);

        if ($attempts >= 3) {
            $response = [
                'status' => 429,
                'pesan' => 'Anda telah mencapai batas kesalahan Login'
            ];

            return response()->json($response,429);
        }
        
        $check_users = User::where('email', '=', $input['email'])->first();
        $userId = DB::table('users')
                ->select('id')
                ->where('email', '=', $input['email'])
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

                return response()->json($response,200);
            }
            else{

                $response = [
                    'status' => 401,
                    'pesan' => 'Password Salah'
                ];

                Cache::put($attemptsKey, $attempts + 1, Carbon::now()->addMinutes(1));
                
                return response()->json($response,401);
            }
        }
        else {
            $response['status'] = 401;
            $response['message'] = 'Gagal Login';

            Cache::put($attemptsKey, $attempts + 1, Carbon::now()->addMinutes(1));

            return response()->json($response, 401);
        }
    }

    public function logout(Request $request){

        $request->user()->token()->delete();
        return response()->json([
            'pesan' => 'User Berhasil Logout'
        ],200);
    }
}
