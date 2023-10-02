<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logactivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class LogActivityController extends Controller
{
    public function log_show()
    {
        try {
            $cekIdUser = auth()->user()->id;
            $query = DB::table('log_activity')
                ->where('user_id', $cekIdUser)
                ->get();

            $response = [
                'status' => 200,
                'message' => 'Ok',
                'data' => $query->toArray(),
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

    public function komship(Request $request)
    {
        $token = "GATEWAYKOMSHIPKOMERCE";

        try {
            $client = new Client();
            $search = $request->input('search'); // Mengambil nilai pencarian dari permintaan

            $response = $client->get("https://dev.komship.komerce.my.id/api/v2/admin/order/search?search={$search}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            $user = User::findOrFail(auth()->user()->id);
            if ($user) {
                $username = $user->username;
                $userId = $user->id;

                $userId = DB::table('users')
                    ->select('id')
                    ->where('username', '=', $username)
                    ->get();

                $log = new Logactivity();
                $log->user_id = $userId[0]->id;
                $log->activity = 'Cek Resi';
                $log->notes = 'Berhasil Cek Resi';
                $log->save();
            }

            $data = $response->getBody()->getContents();
            $jsonData = json_decode($data, true);

            return $jsonData;
        } catch (\Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'data tidak ditemukan',
            ];
            return response()->json($response, $response['status']);
        }
    }
}
