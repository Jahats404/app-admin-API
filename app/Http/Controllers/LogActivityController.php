<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logactivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class LogActivityController extends Controller
{
    public function log_show()
    {
        try {
            $cekIdUser = auth()->user()->id;
            $query = DB::table('log_activity')->get();
            $jmlhData = $query->count();

            for ($i=0; $i < $jmlhData; $i++) { 
                $namaAdmin = DB::table('users')
                            ->select('nama_lengkap')
                            ->where('id', $query[$i]->user_id)
                            ->get();
                $data[] = [
                    'id' => $query[$i]->id,
                    'user_id' => $query[$i]->user_id,
                    'nama_admin' => 'Admin ' . $namaAdmin[0]->nama_lengkap,
                    'activity' => $query[$i]->activity,
                    'notes' => $query[$i]->notes,
                    'created_at' => $query[$i]->created_at,
                    'updated_at' => $query[$i]->updated_at,
                ];
            }
            
            $response = [
                'status' => 200,
                'message' => 'Ok',
                'data' => $data,
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

    public function search(Request $request)
    {
        try {
            $nama = $request->input('search');
            
            $user = Logactivity::where('aktor', 'like', '%' . $nama . '%')->get();
            $response = [
                'status' => 200,
                'message' => 'success',
                'data' => $user->toArray(),
            ];
            return response()->json($response, $response['status']);
            
        } 
        catch (\Throwable $e) {
            $response = [
                'status' => 500,
                'message' => 'data tidak ditemukan',
            ];
            return response()->json($response, $response['status']);
        }
        
    }

    public function filterSearch(Request $request)
    {
        $activity = $request->input('activity');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);


        $search = DB::table('log_activity')
                ->where('activity', $activity)
                ->whereDate('created_at', '>=', $startCarbon)
                ->whereDate('created_at', '<=', $endCarbon)
                ->get();
        dd($search);
    }
}
