<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Envio; // Modelo de Envío
use App\Models\Pedido; // Modelo de Pedido
use App\Models\Usuario; // Usar Usuario en lugar de User si ese es tu modelo de usuario
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Necesario para Rule::in

class DeliveryController extends Controller
{
    /**
     * Muestra los pedidos asignados actualmente al repartidor logueado.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $repartidorId = Auth::id(); // Obtiene el ID del repartidor logueado

        // Pedidos asignados y no entregados (en ruta o asignados sin repartidor_id, aunque el admin lo asigna)
        $pedidosAsignados = Pedido::whereHas('envio', function ($query) use ($repartidorId) {
                $query->where('repartidor_id', $repartidorId)
                      ->whereIn('estado', ['asignado', 'en ruta']); // O solo 'en ruta' si admin cambia el estado
            })
            // CORREGIDO: Usar 'items' en lugar de 'pedidoItems'
            ->with(['usuario', 'envio.repartidor', 'items.producto', 'items.promocion'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('repartidor.dashboard', compact('pedidosAsignados'));
    }

    /**
     * Muestra los detalles de un envío específico para el repartidor.
     *
     * @param  \App\Models\Envio  $envio
     * @return \Illuminate\Http\Response
     */
    public function show(Envio $envio)
    {
        // Asegúrate de que el envío pertenece al repartidor logueado
        if ($envio->repartidor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver este envío.');
        }

        // Cargar relaciones necesarias para la vista de detalles
        // CORREGIDO: Usar 'items' en lugar de 'pedidoItems'
        $envio->load(['pedido.usuario', 'pedido.items.producto', 'pedido.items.promocion', 'repartidor']);

        return view('repartidor.show', compact('envio'));
    }

    /**
     * Muestra el historial de envíos completados por el repartidor logueado.
     *
     * @return \Illuminate\Http\Response
     */
    public function history()
    {
        $repartidorId = Auth::id();

        $historialEnvios = Pedido::whereHas('envio', function ($query) use ($repartidorId) {
                $query->where('repartidor_id', $repartidorId)
                      ->where('estado', 'entregado'); // Solo envíos entregados
            })
            // CORREGIDO: Usar 'items' en lugar de 'pedidoItems'
            ->with(['usuario', 'envio.repartidor', 'items.producto', 'items.promocion'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('repartidor.history', compact('historialEnvios'));
    }

    /**
     * Actualiza el estado de un envío (desde la perspectiva del repartidor).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Envio  $envio
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Envio $envio)
    {
        // Asegúrate de que el envío pertenece al repartidor logueado
        if ($envio->repartidor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para actualizar este envío.');
        }

        $request->validate([
            'estado' => ['required', 'string', Rule::in(['en ruta', 'entregado', 'fallido'])], // Estados que el repartidor puede cambiar
        ]);

        $envio->estado = $request->estado;
        $envio->actualizado_en = now(); // Actualizar el timestamp
        $envio->save();

        // Si el envío se marca como 'entregado', también actualiza el estado del pedido
        if ($envio->estado === 'entregado') {
            $envio->pedido->estado = 'entregado';
            $envio->pedido->save();
        }
        // Puedes añadir lógica para 'fallido' si es necesario.

        return redirect()->route('repartidor.pedidos.show', $envio->id)->with('success', 'Estado del envío actualizado correctamente.');
    }
}