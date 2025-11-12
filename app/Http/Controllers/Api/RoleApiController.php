<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;

class RoleApiController extends Controller
{
    public function index(Request $request)
    {
        $items = QueryBuilder::for(Role::class)
            ->allowedFilters(['nombre'])
            ->allowedSorts(['id', 'created_at'])
            ->paginate();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate(Role::$rules);
        $item = Role::create($data);

        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = Role::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Role::findOrFail($id);
        $data = $request->validate(Role::$rules);
        $item->update($data);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Role::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}