<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;

class PermissionApiController extends Controller
{
    public function index(Request $request)
    {
        $items = QueryBuilder::for(Permission::class)
            ->allowedFilters(['nombre'])
            ->allowedSorts(['id', 'created_at'])
            ->paginate();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate(Permission::$rules);
        $item = Permission::create($data);

        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = Permission::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Permission::findOrFail($id);
        $data = $request->validate(Permission::$rules);
        $item->update($data);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Permission::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}