<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show_profile(){

        try {
            $profile = auth()->user();
            $response = [
                'status' => 200,
                'message' => 'Ok',
                'data' => $profile->toArray(),
            ];
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'message' => 'fail',
            ];
            return response()->json($response,$response['status']);
        }
    }
}
