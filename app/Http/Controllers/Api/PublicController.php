<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PublicController extends Controller
{
    public function index()
    {
        $user = User::all();

        return response()->json([
            "success" => true,
            "message" => "User List",
            "data" => $user
        ]);
    }

    public function indexById($id)
    {
        $user = User::find($id);

        return response()->json([
            "success" => true,
            "message" => "User List",
            "data" => $user
        ]);
    }

    public function create(Request $request)
    {
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        return response()->json([
            "success" => true,
            "message" => "Registered",
            "data" => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->update();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        return response()->json([
            "success" => true,
            "message" => "Updated",
            "data" => $data
        ]);
    }

    public function destroy($id)
    {
        User::find($id)->delete();

        return response()->json([
            "success" => true,
            "message" => "Deleted",
        ]);
    }
}
