<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleMenu extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_menu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'menu_id',
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
     * Get the role that owns the role menu.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the menu that owns the role menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
