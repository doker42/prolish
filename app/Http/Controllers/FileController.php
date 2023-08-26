<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return array
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ($request->get('binary')) ? 'required|image' : 'required|image64:jpeg,jpg,png'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        } else {
            if ($request->get('binary')) {
                $extension = $request->image->getClientOriginalExtension();
                $fileName = Carbon::now()->timestamp . '_' . uniqid() . '.' . $extension;

                $img = Image::make($request->image->getRealPath());

                $img->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save(public_path('images/').$fileName);
            } else {
                $imageData = $request->get('image');
                $extension = explode('/', explode(':', substr($imageData, 0, strpos($imageData, ';')))[1])[1];

                $fileName = Carbon::now()->timestamp . '_' . uniqid() . '.' . $extension;
                Image::make($request->get('image'))->save(public_path('images/').$fileName);
            }

            return response()->json([
                'success' => true,
                'image' => '/images/'.$fileName
            ]);
        }
    }


    public function show(Request $request)
    {
        $file = public_path($request->get('file'));
        $mime = finfo_open(FILEINFO_MIME_TYPE);

        if (!file_exists($file) || (finfo_file($mime, $file) !== 'application/pdf')) {
            return response()->json(array(
                'code'      =>  404,
                'message'   =>  'file not found'
            ), 404);
        }

        return response()->download($file);
    }
}
