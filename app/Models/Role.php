<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the menus that belong to the role.
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu')
                    ->withTimestamps()
                    ->whereNull('role_menu.deleted_at');
    }

    /**
     * Get all role menu access records for this role.
     */
    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class);
    }
}
