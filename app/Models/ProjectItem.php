<?php

namespace App\Models;

use App\Domain\Project\ItemManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ProjectItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'type',
        'view_url',
        'status',
        'job_done_at',
        'size'
    ];

    protected $appends = [
        'icon',
        'urls',
        'can_convert',
        'external_view',
        //'potree_view',
    ];
    public function getExternalViewAttribute()
    {
        if (!empty($this->attributes['view_url']) && (Potree::where('item_id', $this->attributes['id'])->count() > 0 || strpos($this->attributes['view_url'], '3d-viewer/#/') > 0)) {
            return env('APP_URL').'/file/view/'.Crypt::encryptString($this->attributes['id']);
        }

        return false;
    }
   /* public function getPotreeViewAttribute()
    {
        return !empty($this->attributes['id'])&&ItemUrl::where('item_id', $this->attributes['id'])
                ->whereIn('type', config('allowed_types.files.point_clouds.convertable_types'))
                ->count() > 0;
    }*/
    public function getCanConvertAttribute()
    {
        if (env("CC_SUPPORT") && !empty($this->attributes['id']) && $this->attributes['type'] == 'point_clouds') {
            $types = ItemUrl::where('item_id', $this->attributes['id'])->pluck('type')->toArray();

            $can_convert = false;

            foreach ($types as $type) {
                if(in_array($type, ['e57','pts','ptx', 'xyz'])){
                    $can_convert = true;
                }
            }

            if ($can_convert) {
                return array_values(array_diff([
                    'e57',
                    'pts',
                    'ptx',
                    'xyz'
                ], $types));
            } else {
                return ['cant_convert'];
            }
        }

        return [];
    }

    public function getSizeAttribute()
    {
        return number_format($this->attributes['size'], 2, ".", "");
    }

    public function getIconAttribute()
    {
        $types = config('allowed_types');

        foreach ($types as $type => $category) {
            if (!empty($category[$this->attributes['type']])) {
                $this->attributes['belongs_to'] = $type;
                return $category[$this->attributes['type']]['icon'];
            }
        }

        return null;
    }

    public function getUrlsAttribute()
    {
        if (!empty($this->attributes['id'])) {
            return ItemUrl::where('item_id', $this->attributes['id'])->pluck('url', 'type');
        }

        return [];
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id');
    }
    public function setJobDoneAtAttribute($value)
    {
        $this->attributes['job_done_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getJobDoneAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
    public function uploader()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function potree()
    {
        return $this->hasOne('App\Models\Potree', 'item_id');
    }

    public function url()
    {
        return $this->hasMany('App\Models\ItemUrl', 'item_id')->orderByRaw("FIELD(type , 'youtube') DESC");
    }

    public static function manager() :ItemManager
    {
        return ItemManager::up();
    }
}
