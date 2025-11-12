<?php

namespace App\Http\Controllers\Api;

use App\Models\Usuarios;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;

class UsuariosApiController extends Controller
{
    public function index(Request $request)
    {
        $items = QueryBuilder::for(Usuarios::class)
            ->allowedFilters(['nombre'])
            ->allowedSorts(['id', 'created_at'])
            ->paginate();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate(Usuarios::$rules);
        $item = Usuarios::create($data);

        return response()->json($item, 201);
    }

    public function show($id)
    {
        $item = Usuarios::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Usuarios::findOrFail($id);
        $data = $request->validate(Usuarios::$rules);
        $item->update($data);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Usuarios::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}