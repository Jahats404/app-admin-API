<?php

namespace App\Http\Controllers;

use Countable;
use App\Models\User;
use App\Models\Admin;
use App\Models\Logactivity;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

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
        $response = [
            'status' => 200,
            'pesan' => 'Ok',
            'data' => $query->toArray(),
        ];

        return response()->json($response, $response['status']);
    }

    public function sendVerifyEmail(Request $request)
    {
        if (auth()->user()) {
            $email = $request->email;
            $user = User::where('email', $email)->get();
            $cekVerify = DB::table('users')
                        ->select('email_verified_at')
                        ->where('email',$email)
                        ->get();
            
            if (count($user) > 0) {

                if ($cekVerify[0]->email_verified_at != null) {
                    $response = [
                        'status' => 401,
                        'message' => 'email has been verified',
                    ];
                    return response()->json($response,$response['status']);
                } 
                else {
                    $random = Str::random(40);
                    $domain = URL::to('/');
                    $url = $domain . '/verify-mail/' . $random;

    
                    $data['url'] = $url;
                    $data['email'] = $email;
                    $data['title'] = "Email Verification";
                    $data['body'] = "Please click here to below to verify your Mail.";
    
                    Mail::send('verifyEmail', ['data' => $data], function($message) use ($data){
                        $message->to($data['email'])->subject($data['title']);
                    });
    
                    $user = User::find($user[0]['id']);
                    $user->remember_token = $random;
                    $user->save();

                    $id = auth()->user()->id;
                    $log = new Logactivity();
                        $log->user_id = $id;
                        $log->activity = 'Verify Email';
                        $log->notes = 'verification has been sent to the email ' . $email;
                        $log->save();
    
                    $response = [
                        'status' => 200,
                        'message' => 'Mail sent successfull',
                    ];
                    return response()->json($response,$response['status']);
                }
                

            } else {
                $response = [
                    'status' => 404,
                    'message' => 'Email not found',
                ];
                return response()->json($response,$response['status']);
            }
            
        }
        else {
            $response = [
                'status' => 401,
                'message' => 'unauthorized',
            ];
            return response()->json($response,$response['message']);
        }
    }

    public function verificationMail($token)
    {
        $user = User::where('remember_token',$token)->get();
        if (count($user) > 0) {
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            $user = User::find($user[0]['id']);
            $user->remember_token = '';
            $user->email_verified_at = $datetime;
            $user->save();

            $response = [
                'status' => 200,
                'message' => 'Email verified successfully.',
            ];
            return response()->json($response,$response['status']);
        } else {
            $response = [
                'status' => 404,
                'message' => 'Not Found.',
            ];
            return response()->json($response,$response['status']);
        }
        
    }

    public function forgetPassword(Request $request)
    {
        try {
            
            $user = User::where('email', $request->email)->get();
            if (count($user) > 0) {
                $token = Str::random(40);
                $domain = URL::to('/');
                $url = $domain . '/reset-password?token=' . $token;
                
                $data['url'] = $url;
                $data['email'] = $request->email;
                $data['title'] = "Password Reset";
                $data['body'] = "Please click on below link to reset password";
                
                Mail::send('forgetPasswordMail', ['data' => $data], function($message) use ($data){
                    $message->to($data['email'])->subject($data['title']);
                });
                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                
                PasswordReset::updateOrCreate(
                    [
                        'email' => $request->email,
                    ],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => $datetime,
                ]);

                $response = [
                    'status' => 200,
                    'message' => 'Mail sent successfull, please check your email to reset password.',
                ];
                return response()->json($response,$response['status']);
                
            }
            else {
                $response = [
                    'status' => 200,
                    'message' => 'User Not Found',
                ];
                return response()->json($response,$response['status']);
            }

        } catch (\Throwable $e) {
            $response = [
                'status' => 500,
                'message' => 'fail',
            ];
            return response()->json($response,$response['status']);
        }
    }

    public function resetPasswordLoad(Request $request)
    {
        $resetData = PasswordReset::where('token',$request->token)->get();
        if(isset($request->token) && count($resetData) > 0){

            $user = User::where('email',$resetData[0]['email'])->get();
            // return view('resetPassword',compact('user'));
            $id = $user[0]['id'];
            $response = [
                'status' => 200,
                'id user' => $id,
                'message' => 'success',
            ];
            return response()->json($response,$response['status']);
            
        }
        else {
            $response = [
                'status' => 404,
                'message' => 'Not Found.',
            ];
            return response()->json($response,$response['status']);
        }
    }

    public function resetPassword(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Error',
                'error' => $validator->errors(),
            ], 401);
        }

        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        PasswordReset::where('email',$user->email)->delete();

        $response = [
            'status' => 200,
            'message' => 'your password has been reset successfully.',
        ];
        return response()->json($response,$response['status']);
    }
}
