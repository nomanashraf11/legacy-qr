<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testvideo extends Controller
{
    public function testvideo()
    {
        return response()->json(['message' => 'Test video endpoint']);
    }
}
