<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectGalleryImage extends Model
{
    protected $fillable = [
        'project_id',
        'url',
        'folder_id'
    ];

    public function folders()
    {
        return $this->hasOne('App\Models\ProjectGalleryFolder', 'id','folder_id');
    }
}
