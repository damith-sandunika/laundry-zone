<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = \App\Models\Promotion::orderBy('order_sequence')->get();
        return view('promotions.index', compact('promotions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'rapid_laundromat/promotions',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $path = $result['secure_url'];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Cloudinary Upload Failed in Promotion: ' . $e->getMessage());
                    $path = $file->store('promotions', 'public');
                }
            } else {
                $path = $file->store('promotions', 'public');
            }
        }

        \App\Models\Promotion::create([
            'title' => $request->title,
            'image_path' => $path,
            'order_sequence' => \App\Models\Promotion::max('order_sequence') + 1,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Promotion added successfully.');
    }

    public function update(Request $request, \App\Models\Promotion $promotion)
    {
        $promotion->update(['is_active' => $request->has('is_active')]);
        return redirect()->back()->with('success', 'Promotion updated.');
    }

    public function destroy(\App\Models\Promotion $promotion)
    {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($promotion->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($promotion->image_path);
        }
        $promotion->delete();
        return redirect()->back()->with('success', 'Promotion deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:promotions,id',
            'order.*.sequence' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            \App\Models\Promotion::where('id', $item['id'])->update(['order_sequence' => $item['sequence']]);
        }

        return response()->json(['success' => true]);
    }
}

