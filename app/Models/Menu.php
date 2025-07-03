<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'path',
        'icon',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the roles that belong to the menu.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu')
                    ->withTimestamps()
                    ->whereNull('role_menu.deleted_at');
    }

    /**
     * Get all role menu access records for this menu.
     */
    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class);
    }
}
