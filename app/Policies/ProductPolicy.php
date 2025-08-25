<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $roles = $user->roles()->with('permissions')->get();
        $role_names = $roles->pluck('name')->implode(',');
        if($role_names->contains('admin')){
            return true;
        }
        $permissions=$roles->pluck('permissions')->flatten()->pluck('name');
        if($permissions->contains('products.view')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        $roles = $user->roles()->with('permissions')->get();
        $role_names = $roles->pluck('name')->implode(',');
        if($role_names->contains('admin')){
            return true;
        }
        $permissions=$roles->pluck('permissions')->flatten()->pluck('name');
        if($permissions->contains('products.view')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $roles = $user->roles()->with('permissions')->get();
        $role_names = $roles->pluck('name')->implode(',');
        if($role_names->contains('admin')){
            return true;
        }
        $permissions=$roles->pluck('permissions')->flatten()->pluck('name');
        if($permissions->contains('products.create')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        $roles = $user->roles()->with('permissions')->get();
        $role_names = $roles->pluck('name')->implode(',');
        if($role_names->contains('admin')){
            return true;
        }
        $permissions=$roles->pluck('permissions')->flatten()->pluck('name');
        if($permissions->contains('products.edit')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        $roles = $user->roles()->with('permissions')->get();
        $role_names = $roles->pluck('name')->implode(',');
        if($role_names->contains('admin')){
            return true;
        }
        $permissions=$roles->pluck('permissions')->flatten()->pluck('name');
        if($permissions->contains('products.create')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
