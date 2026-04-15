<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Room;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $featuredRooms = Room::with(['branch', 'images'])->where('is_available', true)->take(6)->get();
        return view('welcome', compact('branches', 'featuredRooms'));
    }

    public function branchDetail(Branch $branch)
    {
        $rooms = $branch->rooms()->where('is_available', true)->with('images')->get();
        return view('branch-detail', compact('branch', 'rooms'));
    }

    public function roomDetail(Room $room)
    {
        $room->load(['branch', 'images', 'facilities']);
        return view('room-detail', compact('room'));
    }
}
