<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Menu::with(['roles']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('path', 'ILIKE', "%{$search}%");
            });
        }

        // Include trashed records if requested
        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        // Order by sort_order
        $query->orderBy('sort_order', 'asc');

        $menus = $query->paginate($request->get('per_page', 15));

        return response()->json($menus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'path' => 'required|string|max:255|unique:menus,path',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'sometimes|integer|min:0',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Menu::max('sort_order') + 1;
        }

        $menu = Menu::create([
            'name' => $validated['name'],
            'path' => $validated['path'],
            'icon' => $validated['icon'] ?? null,
            'sort_order' => $validated['sort_order'],
        ]);

        // Attach roles if provided
        if (isset($validated['role_ids'])) {
            $menu->roles()->sync($validated['role_ids']);
        }

        $menu->load(['roles']);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => $menu
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        $menu->load(['roles', 'roleMenus']);
        return response()->json($menu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'path' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('menus')->ignore($menu->id)],
            'icon' => 'sometimes|nullable|string|max:100',
            'sort_order' => 'sometimes|integer|min:0',
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $menu->update([
            'name' => $validated['name'] ?? $menu->name,
            'path' => $validated['path'] ?? $menu->path,
            'icon' => $validated['icon'] ?? $menu->icon,
            'sort_order' => $validated['sort_order'] ?? $menu->sort_order,
        ]);

        // Update role associations if provided
        if (isset($validated['role_ids'])) {
            $menu->roles()->sync($validated['role_ids']);
        }

        $menu->load(['roles']);

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => $menu
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted menu.
     */
    public function restore($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->restore();

        return response()->json([
            'message' => 'Menu restored successfully',
            'menu' => $menu
        ]);
    }

    /**
     * Permanently delete a menu.
     */
    public function forceDelete($id)
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->forceDelete();

        return response()->json([
            'message' => 'Menu permanently deleted'
        ]);
    }

    /**
     * Get trashed menus.
     */
    public function trashed(Request $request)
    {
        $query = Menu::onlyTrashed()->with(['roles']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('path', 'ILIKE', "%{$search}%");
            });
        }

        $query->orderBy('sort_order', 'asc');
        $menus = $query->paginate($request->get('per_page', 15));

        return response()->json($menus);
    }

    /**
     * Reorder menus by updating sort_order.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:menus,id',
            'menus.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['menus'] as $menuData) {
            Menu::where('id', $menuData['id'])
                ->update(['sort_order' => $menuData['sort_order']]);
        }

        return response()->json([
            'message' => 'Menus reordered successfully'
        ]);
    }
}
