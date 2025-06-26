<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HomeImage;

class HomeImageController extends Controller
{
    public function index()
    {
        $homeImages = HomeImage::orderBy('order')->get();
        return view('home', compact('homeImages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer'
        ]);

        $path = $request->file('image')->store('img/home', 'public');

        HomeImage::create([
            'image_path' => $path,
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order ?? 0
        ]);

        return back()->with('success', 'Imagen agregada correctamente');
    }
}