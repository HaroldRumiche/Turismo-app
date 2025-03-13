<?php

namespace App\Http\Controllers;

use App\Models\Tourist;
use Illuminate\Http\Request;

class TouristController extends Controller
{
    public function index()
    {
        $tourists = Tourist::all();
        // Si usas Inertia, devuelve los datos así:
        return response()->json([
            'data' => $tourists,
            'total' => count($tourists)
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
        ]);
        
        $tourist = Tourist::create($request->all());
        return response()->json($tourist, 201);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
        ]);
        
        $tourist = Tourist::findOrFail($id);
        $tourist->update($request->all());
        return response()->json($tourist);
    }
    
    public function destroy($id)
    {
        $tourist = Tourist::findOrFail($id);
        $tourist->delete();
        return response()->json(['success' => true]);
    }
}