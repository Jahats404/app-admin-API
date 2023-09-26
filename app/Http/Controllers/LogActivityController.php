<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logactivity;
use Illuminate\Support\Facades\DB;

class LogActivityController extends Controller
{
    public function log_show(){
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
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'message' => 'fail',


                // test branch
                // test branch 2

            ];
            return response()->json($response,$response['status']);
        } // ini coment
        // ini coment 2git 
        // ini coment 2
        // ini coment 3
        
    }
}
