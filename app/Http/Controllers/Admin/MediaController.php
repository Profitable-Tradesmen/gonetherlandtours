<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ImageService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        return view('admin.media', ['items' => Media::latest()->paginate(24)]);
    }

    public function store(Request $request, ImageService $images)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
            'alt' => 'required|string|max:190',
        ], ['alt.required' => 'Alt text is required: describe what the image shows.']);
        $images->store($request->file('file'), trim(strip_tags($request->alt)));

        return back()->with('status', 'Image uploaded and converted to WebP.');
    }

    public function update(Request $request, Media $media)
    {
        $media->update($request->validate(['alt' => 'required|string|max:190']));

        return back()->with('status', 'Alt text saved.');
    }

    public function destroy(Media $media, ImageService $images)
    {
        $images->delete($media);

        return back()->with('status', 'Image deleted.');
    }
}
