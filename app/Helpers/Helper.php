<?php

namespace App\Helpers;

class Helper
{

    // public static function metaTags($page)
    // {
    //     $metas = null;
    //     if ($page) {
    //         $metas = MetaTag::where('page', $page)->first();
    //     }
    //     return $metas;
    // }

    public static function uploadImage($photo, $folder = 'photos')
    {
        if ($photo) {
            $fileName       = Helper::renameFile($photo->getClientOriginalName());
            /*** Public Folder Upload */
            $folder_path    = 'uploads/' . $folder;
            $photo_path     = $folder_path . '/' . $fileName;
            $photo_stored   = $photo->move($folder_path, $fileName);
            if ($photo_stored) {
                return [
                    'status' => true,
                    'name' => $photo_path,
                ];
            }
        }
        return [
            'status' => false,
            'name' => '',
        ];
    }

    public static function renameFile($full_filename = '')
    {
        $random = date('Ymd-His') . '-' . floor(microtime(true) * 10000);
        $filename = $random . ".jpg";
        if ($full_filename) {
            $exploded_name  = explode('.', $full_filename);
            $filename       = $random . "." . end($exploded_name);
        }
        return $filename;
    }

    public static function unlinkImage($photo_name)
    {
        if (file_exists(public_path($photo_name))) {
            unlink(public_path($photo_name));
        }
    }
}