<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class GDriveUpload
{
    /**
     * Upload file to GDrive and return path
     *
     * @param  string  $filename
     * @param  string  $content
     *
     * @return string GDrive path
     */

    static function upload($filename, $content)
    {
        Storage::disk('google')->put($filename, $content, 'public');
        $contents = collect(Storage::cloud()->listContents('/', false));

        // Get file details...
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first();

        return $file['path'];
    }
}