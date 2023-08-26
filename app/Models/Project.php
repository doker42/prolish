<?php

namespace App\Models;

use App\Domain\Project\ProjectManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    const DEFAULT_LOGO = '/images/450x450.png';

    protected $fillable = [
        'title',
        'description',
        'image',
        'status',
        'company_id',
        'address',
        'geo_point',
        'public',
        'size'
    ];

    protected $appends = [
        'favourite',
        'summary',
        'company',
        'gallery_items',
        'size_gb'
    ];

    protected $casts = [
        'geo_point' => 'array',
        'public' => 'boolean'
    ];

    public function getFavouriteAttribute()
    {
        if (Auth::check()) {
            return UserFavouriteProject::where(['user_id' => Auth::user()->id, 'project_id' => $this->attributes['id']])->exists();
        }

        return false;
    }


    public function getSizeAttribute()
    {
        return number_format($this->attributes['size'], 2, ".", "");
    }

    public function getSizeGbAttribute(){

        return isset($this->attributes['size'])?number_format($this->attributes['size']/ 1024, 2, ".", ""):0;
    }

    public function getSummaryAttribute()
    {
        return ProjectItem::where('project_id', $this->attributes['id'])->get(['type'])->groupBy('icon');
    }

    public function getGalleryItemsAttribute()
    {
        return ProjectGalleryImage::where('project_id', $this->attributes['id'])->count();
    }

    public function getCompanyAttribute()
    {
        return Company::find($this->attributes['company_id']);
    }

    public function items()
    {
        return $this->hasMany('App\Models\ProjectItem', 'project_id');
    }

    public function contacts()
    {
        return $this->hasMany('App\Models\Contact', 'project_id');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\ProjectLog', 'project_id');
    }

    public function gallery_images()
    {
        return $this->hasMany('App\Models\ProjectGalleryImage', 'project_id');
    }

    public function gallery_folders()
    {
        return $this->hasMany('App\Models\ProjectGalleryFolder', 'project_id');
    }

    public function visibilities()
    {
        return $this->hasMany('App\Models\ProjectVisibility', 'project_id');
    }

    public static function manager(): ProjectManager
    {
        return ProjectManager::up();
    }

}
