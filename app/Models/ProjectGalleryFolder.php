<?php

namespace App\Models;

use App\Domain\Project\GalleryManager;
use Illuminate\Database\Eloquent\Model;

class ProjectGalleryFolder extends Model
{
    protected $fillable = [
        'project_id',
        'title'
    ];

    public static function manager(): GalleryManager
    {
        return GalleryManager::up();
    }
}
