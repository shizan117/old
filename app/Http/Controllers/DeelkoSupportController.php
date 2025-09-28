<?php

namespace App\Http\Controllers;

use App\DeelkoSupport;
use Illuminate\Http\Request;

class DeelkoSupportController extends Controller
{
    public function index(Request $request)
    {
        $all_youTube_links = DeelkoSupport::all();
        return view("admin.pages.help", compact('all_youTube_links'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'url_endpoint' => 'required|unique:deelko_supports,url_endpoint',
            'youTube_share_link' => 'required'
        ]);

        $createNewLink = DeelkoSupport::create([
           'url_endpoint' => trim(trim(strtolower($validatedData['url_endpoint'])), '/'),   
            'youTube_share_link' => $validatedData['youTube_share_link']
        ]);

        if (!$createNewLink) {
            return redirect()->back()->with("error", "YouTube Link insertion failed!");
        }
        return redirect()->back()->with("success", "YouTube Link inserted successfully!");
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'select_end_point' => 'required|exists:deelko_supports,id',
            'update_youtube_link' => 'required'
        ]);

        $existingLink = DeelkoSupport::find($validatedData['select_end_point']);

        if (!$existingLink) {
            return redirect()->back()->with("error", "Selected endpoint does not exist!");
        }

        $existingLink->youtube_share_link = $validatedData['update_youtube_link'];

        if (!$existingLink->save()) {
            return redirect()->back()->with("error", "YouTube Link update failed!");
        }
        return redirect()->back()->with("success", "YouTube Link updated successfully!");
    }

    public function distroy(Request $request)
    {
        $validatedData = $request->validate([
            'delete_link_id' => 'required|exists:deelko_supports,id'
        ]);

        $existingLink = DeelkoSupport::find($validatedData['delete_link_id']);

        if (!$existingLink) {
            return redirect()->back()->with("error", "Selected endpoint does not exist!");
        }

        if (!$existingLink->delete()) {
            return redirect()->back()->with("error", "YouTube Link deleting failed!");
        }
        return redirect()->back()->with("success", "YouTube Link deleted successfully!");
    }
}
