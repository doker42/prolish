<?php

namespace App\Models;

use App\Domain\Project\ItemUrlManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ItemUrl extends Model
{
    protected $fillable = [
        'item_id',
        'url',
        'type',
        'size'
    ];

    protected $appends = [
        'embed',
        'filename',
        'external_url',
        'is_image',
    ];

    protected $item_image_types = ['jpg', 'jpeg', 'gif', 'tif', 'png', 'svg'];

    public function getExternalUrlAttribute($value)
    {
        if (file_exists(public_path($this->attributes['url']))) {
            return env('APP_URL').'/file/upload/'.Crypt::encryptString($this->attributes['url']);
        }

        return $value;
    }
    public function getIsImageAttribute()
    {
        return in_array($this->attributes['type'], $this->item_image_types);
    }
//    public function getUrlAttribute($value)
//    {
//        if (preg_match('/\.pdf/i', $value)) {
//            $value = '/download?file=' . urlencode($value);
//        }
//
//        return $value;
//    }

    public function getSizeAttribute()
    {
        return number_format($this->attributes['size'], 2, ".", "");
    }

    public function getEmbedAttribute()
    {
        if ($this->attributes['type'] == 'youtube' || $this->attributes['type'] == 'video') {
            if (preg_match('/youtube\.com/i', $this->attributes['url'])) {
                $id = $this->getYoutubeIDfromUrl($this->attributes['url']);
                return '<iframe width="640" height="360" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
            } else {
                $id = (int)substr(parse_url($this->attributes['url'], PHP_URL_PATH), 1);
                return '<iframe src="https://player.vimeo.com/video/' . $id . '" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
            }
        }

        return null;
    }

    private function getYoutubeIDfromUrl($url) {
        if (stristr($url,'youtu.be/')) {
            preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID);
            return $final_ID[4];
        }

        @preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD);
        return $IDD[5];
    }

    public function getFilenameAttribute() {
        if (filter_var($this->attributes['url'], FILTER_VALIDATE_URL)) {
            return $this->attributes['url'];
        }
        return last(explode('/', $this->attributes['url']));
    }

    public static function manager():ItemUrlManager
    {
        return ItemUrlManager::up();
    }
}
