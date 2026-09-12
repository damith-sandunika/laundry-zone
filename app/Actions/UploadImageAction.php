<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadImageAction
{
    /**
     * Upload an image to Cloudinary or local storage fallback.
     *
     * @param UploadedFile $file
     * @param string $folder Cloudinary folder or local folder name
     * @param string $prefix Prefix for local fallback filename
     * @return string Path to the uploaded image
     */
    public function execute(UploadedFile $file, string $folder, string $prefix = 'img'): string
    {
        if (env('CLOUDINARY_URL')) {
            try {
                $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => '365_laundry/' . $folder,
                    'format' => 'webp',
                    'quality' => 'auto'
                ]);
                return $result['secure_url'];
            } catch (\Exception $e) {
                Log::error("Cloudinary Upload Failed in {$folder}: " . $e->getMessage());
            }
        }

        // Fallback to local storage
        $filename = time() . '_' . $prefix . '_' . $file->getClientOriginalName();
        $file->move(public_path($folder), $filename);
        return $folder . '/' . $filename;
    }
}
