<?php

use App\Services\SupabaseService;

if (!function_exists('uploadToSupabase')) {
    function uploadToSupabase($file, $folder = 'posts')
    {
        if (!$file) return null;

        $supabase = app(SupabaseService::class);
        $storage = $supabase->storage();

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $fileName;

        $result = $storage->upload(
            'badnet', 
            $path, 
            $file->get(), 
            $file->getMimeType()
        );

        if ($result['success']) {
            return $result['full_url'];
        }

        return null;
    }
}