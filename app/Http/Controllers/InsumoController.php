<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $insumos = Insumo::when($request->search, function ($query, $search) {
                $query->where('nombre', 'like', '%' . $search . '%');
            })->get();


            return response()->json([
                'html' => view('admin.inventario.partials.tabla-insumos', compact('insumos'))->render()
            ]);
        }

        $insumos = Insumo::all();
        return view('admin.inventario.index', compact('insumos'));
    }
    public function create()
    {
        return view('admin.inventario.create');
    }
    public function store(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255', 'unidad' => 'required|string|max:50', 'cantidad' => 'required|numeric|min:0',]);
        Insumo::create(['nombre' => $request->nombre, 'unidad' => $request->unidad, 'cantidad' => $request->cantidad,]);
        return redirect()->route('inventario.index')->with('success', 'Insumo creado correctamente.');
    }
    public function edit(Insumo $insumo)
    {
        return view('admin.inventario.edit', compact('insumo'));
    }
    public function update(Request $request, Insumo $insumo)
    {
        $request->validate(['nombre' => 'required|string|max:255', 'unidad' => 'required|string|max:50', 'cantidad' => 'required|numeric|min:0',]);
        $insumo->update(['nombre' => $request->nombre, 'unidad' => $request->unidad, 'cantidad' => $request->cantidad,]);
        return redirect()->route('inventario.index')->with('success', 'Insumo actualizado correctamente.');
    }
    public function destroy(Insumo $insumo)
    {
        $insumo->delete();
        return redirect()->route('inventario.index')->with('success', 'Insumo eliminado correctamente.');
    }
}
