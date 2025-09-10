<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="Permission management operations"
 * )
 *
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1, description="Unique permission identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="users.view", description="Permission name"),
 *     @OA\Property(property="display_name", type="string", maxLength=255, nullable=true, example="View Users", description="Permission display name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Permission to view user information", description="Permission description"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Permission creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Permission last update timestamp")
 * )
 */
class PermissionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
