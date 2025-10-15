<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionModule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'order',
    ];

    /**
     * Get the permissions for this module.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}
