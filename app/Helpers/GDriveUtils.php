<?php

namespace App\Helpers;

class GDriveUtils
{

    const GOOGLE_FILE_TYPE = 'file';
    const GOOGLE_DOC_TYPE = 'document';
    const GOOGLE_SPREADSHEET_TYPE = 'spreadsheets';

    static function getExt($id)
    {
        $url = 'https://www.googleapis.com/drive/v2/files/'. $id. '?key=' . env('GOOGLE_API_TOKEN');
        $json = json_decode(file_get_contents($url));

        if (!empty($json->fileExtension)) {
            return $json->fileExtension;
        }

        return null;
    }

    static function getType($url){
        if (preg_match('/\?id=/i', $url) ||
            preg_match('/file\/d\//i', $url)) {
            return self::GOOGLE_FILE_TYPE;
        } else if (preg_match('/document\/d\//i', $url)) {
            return self::GOOGLE_DOC_TYPE;
        } else if (preg_match('/spreadsheets\/d\//i', $url)) {
            return self::GOOGLE_SPREADSHEET_TYPE;
        }

        return null;
    }

    static function getId($url)
    {
        if (preg_match('/\?id=/i', $url)) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            parse_str($query_str, $query_params);

            return $query_params['id'];
        } else if (preg_match('/file\/d\//i', $url)) {
            $split = explode('/', $url);

            return $split[5];
        }

        return null;
    }
}