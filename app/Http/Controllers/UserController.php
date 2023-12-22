<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Logactivity;
use GuzzleHttp\Client;


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
        // dd($user);
        $validator = Validator::make($input, [
            'email' => 'required|email|' . Rule::exists('users', 'email'),
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 404,
                'message' => $validator->errors(),
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
            $cekUser = DB::table('users')->where('no_hp', $request->nohp_lama)->get();
            $validator = Validator::make($request->all(), [
                'nohp_baru' => 'required|numeric|digits_between:10,13|unique:users,no_hp,',
            ]);
            if ($validator->fails()) {
                $response = [
                    'status' => 400,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ];
            } else {
                if ($cekUser[0]->no_hp === $request->nohp_baru) {
                    $response = [
                        'status' => 400,
                        'message' => 'No Hp sama dengan sebelumnya',
                    ];
                } else {
                    
                    $noHp = $request->nohp_baru;
                    $firstDigit = substr($noHp, 0 ,1);
                    if ($firstDigit == "0") {
                        $nohpFix = $noHp;
                        $user = DB::table('users')
                                    ->where('no_hp', $request->nohp_lama)
                                    ->update(['no_hp' => $nohpFix]);
                    }
                    else {
                        $nohpFix = "0" . $noHp;
                        $user = DB::table('users')
                                    ->where('no_hp', $request->nohp_lama)
                                    ->update(['no_hp' => $nohpFix]);
                    }
                    $id = auth()->user()->id;
                    $namaAdmin = DB::table('users')->select('nama_lengkap')->where('id', $id)->get();
                    $log = Logactivity::create([
                        'user_id' => $id,
                        'activity' => 'Update No HP',
                        'notes' => $nohpFix,
                        'aktor' => $namaAdmin[0]->nama_lengkap,
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

    public function verifMail(Request $request) {

        $client = new Client();
        $email = $request->email;
        $product_name = $request->product_name;
        $data = [
            "email" => $email,
            "product_name" => $product_name,
        ];

        $response = $client->request("POST", "https://api.internal.komerce.my.id/auth/api/v1/auth/resend-verification", [
            "json" => $data,
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $jsonResponse = json_decode($body, true);
        dd($jsonResponse);
        
    }
}
