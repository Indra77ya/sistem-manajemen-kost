<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Branch;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::where('status', 'available');

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $rooms = $query->with('branch')->get();
        $branches = Branch::all();

        return view('welcome', compact('rooms', 'branches'));
    }
}
