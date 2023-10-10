<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Membership;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use League\Flysystem\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class UploadController extends Controller
{
    /**
     * Handles the file upload
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws UploadMissingFileException
     * @throws \Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException
     */
    public function upload(Request $request) {
        ini_set('upload_max_filesize', '30240M');
        ini_set('post_max_size', '30240M');
        ini_set('max_input_time', 60000);
        ini_set('max_execution_time', 60000);

        if ($request->get('dztotalfilesize') && $request->get('project_id')|| $request->get('temp_storage', false)) {
            if ($request->get('project_id', false)){
                $project = Project::find($request->get('project_id'));
                $company_id = $project->company_id;
            }
            if ($request->get('temp_storage', false)){
                $company_id = Auth::user()->company_id;
            }
            $membership_limits = Company::manager()->checkAddingResult($company_id, 'space', $request->get('dztotalfilesize'));
            if (!$membership_limits['result']) {
                return response()->json($membership_limits, 403);
            }
        }

        // create the file receiver
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));


        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need, current example uses `move` function. If you are
            // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            return $this->saveFile(
                $save->getFile(),
                $request->get('project_id', 0),
                $request->get('temp_storage', false) ? $company_id : false,
                $request->get('index', 0)
            );
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
            'status' => true
        ]);
    }

    private function canUpload($filesize, $project_id)
    {
        $project = Project::find($project_id);
        $company = Company::find($project->company_id);

        $size = $company->storage_used ?? 0;
        $size += $filesize;

        $membership_size = 0;
        if ($membership = Membership::find($company->membership_id)) {
            $membership_size = $membership->size*1024*1024 ?? 0;
        }

        return ($size > $membership_size) ? false : true;
    }

    /**
     * Saves the file
     *
     * @param UploadedFile $file
     * @param $project_id
     * @param $company_storage_id
     * @param $index
     * @return \Illuminate\Http\JsonResponse
     */
    protected function saveFile(UploadedFile $file, $project_id, $company_storage_id, $index)
    {
        ini_set('upload_max_filesize', '30240M');
        ini_set('post_max_size', '30240M');
        ini_set('max_input_time', 60000);
        ini_set('max_execution_time', 60000);
        
        $mime = str_replace('/', '-', $file->getMimeType());

        $filename = str_replace(' ', '_', $file->getClientOriginalName());
        if ($company_storage_id){
            $webdav = Company::manager()->getWebDavAdapter(Auth::user()->company);
            if($webdav->has($filename)){
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            }
            $config['baseUri'] = env('NEXTCLOUD_API_URL');
            $config['userName'] = Auth::user()->company->temporary_folder;
            $config['password'] = Auth::user()->company->storage_pass;
            $config['pathPrefix'] ='remote.php/dav/files/'.Auth::user()->company->temporary_folder;
            $config_syst = new Config($config);
            $contents = file_get_contents($file);
            $webdav->write($filename, $contents, $config_syst);
            $filepath = $filename;
        } else {
            $filepath = 'uploads/documents/' . $project_id . '/';
            if (file_exists(public_path($filepath) . $filename)) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            }

            // move the file name
            $file->move(public_path($filepath), $filename);
        }

        ActivityLog::create([
            'user_id' => Auth::user()->id,
            'action' => 'file_uploaded',
            'data' => $project_id,
        ]);

//        $dir = public_path() . '/chunks/';
//        if (File::isDirectory($dir)) {
//            File::cleanDirectory($dir);
//        }

        return response()->json([
            'path' => $filepath,
            'name' => $filename,
            'mime_type' => $mime,
            'index' => $index
        ]);
    }
}
