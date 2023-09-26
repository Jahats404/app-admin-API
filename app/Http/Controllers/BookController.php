<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;


class BookController extends Controller
{
    public function book_show(Request $request){

        try {
            $input = $request->all();
        
            $user = auth()->user()->id;
            $users = json_decode(json_encode($user), true);
            $query = Book::get();

            unset($query['timestamps']);
            $response = [
                'status' => 200,
                'pesan' => 'Ok',
                'data' => $query->toArray(),
            ];

            return response()->json($response, $response['status']);
        } 
        catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'pesan' => 'gagal',
            ];
    
            return response()->json($response, $response['status']);
        }
        
    }

    public function book_store(Request $request){
        try {
            $book = new Book();
            $book->subjek = $request->subjek;
            $book->kuantitas = $request->kuantitas;
            $book->save();

            $response = [
                'status' => 200,
                'pesan' => 'Ok',
            ];

            return response()->json($response, $response['status']);

        } catch (\Throwable $th) {
            $response = [
                'status' => 500,
                'pesan' => 'gagal',
            ];
            return response()->json($response, $response['status']);
        }

    }
}
