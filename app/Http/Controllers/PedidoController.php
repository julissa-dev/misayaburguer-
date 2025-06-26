<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\MovimientoInsumo;
use App\Models\Insumo;
use App\Models\Usuario;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function mostrar(Pedido $pedido)
    {
        if ($pedido->usuario_id !== Auth::id()) {
            return response()->json(['success' => false], 403);
        }

        $pedido->load([
            'items.producto',
            'pago' => fn($q) => $q->latest(),
            'envio'
        ]);

        return response()->json([
            'success' => true,
            'pedido' => $pedido,
        ]);
    }

    

    public function adminPedidos(Request $request)
    {
        $query = Pedido::with(['usuario', 'envio.repartidor'])
                        ->orderBy('created_at', 'desc');

        $estadoFiltro = $request->query('estado_filtro');

        if ($estadoFiltro && $estadoFiltro !== 'todos') {
            $estadosPedidoValidos = ['en preparacion', 'en camino', 'entregado', 'cancelado'];
            if (in_array($estadoFiltro, $estadosPedidoValidos)) {
                $query->where('estado', $estadoFiltro);
            }
            $estadosEnvioValidos = ['asignado', 'en ruta', 'entregado'];
            if (in_array($estadoFiltro, $estadosEnvioValidos)) {
                $query->whereHas('envio', function ($q) use ($estadoFiltro) {
                    $q->where('estado', $estadoFiltro);
                });
            }
            if ($estadoFiltro === 'sin_asignar_repartidor') {
                $query->whereHas('envio', function ($q) {
                    $q->where('estado', 'asignado')->whereNull('repartidor_id');
                });
            }
            if ($estadoFiltro === 'con_repartidor_asignado') {
                $query->whereHas('envio', function ($q) {
                    $q->whereNotNull('repartidor_id');
                });
            }
        }

        $pedidos = $query->paginate(10)->appends(request()->query());

        $estadosDisponibles = [
            'todos' => 'Todos los Pedidos',
            'en preparacion' => 'Pedido: En Preparación',
            'en camino' => 'Pedido: En Camino',
            'entregado' => 'Pedido: Entregado',
            'cancelado' => 'Pedido: Cancelado',
            'asignado' => 'Envío: Asignado (Pendiente de Repartidor)',
            'en ruta' => 'Envío: En Ruta',
            'sin_asignar_repartidor' => 'Envío: Sin Asignar Repartidor',
            'con_repartidor_asignado' => 'Envío: Con Repartidor Asignado',
        ];

        return view('admin.pedidos.index', compact('pedidos', 'estadosDisponibles', 'estadoFiltro'));
    }

    private function getAvailableRepartidores()
    {
        $repartidoresOcupadosIds = Envio::whereIn('estado', ['asignado', 'en ruta'])
                                        ->whereNotNull('repartidor_id')
                                        ->pluck('repartidor_id')
                                        ->unique();

        $repartidoresDisponibles = Usuario::where('rol', 'repartidor')
                                          ->whereNotIn('id', $repartidoresOcupadosIds)
                                          ->get();

        return $repartidoresDisponibles;
    }

    public function showAssignRepartidorForm(Pedido $pedido)
    {
        if (!$pedido->envio) {
            return redirect()->route('admin.pedidos.index')->with('error', 'Este pedido no tiene un registro de envío para asignar.');
        }

        $repartidores = $this->getAvailableRepartidores();

        return view('admin.pedidos.assign_repartidor', compact('pedido', 'repartidores'));
    }

    public function assignRepartidor(Request $request, Pedido $pedido)
    {
        $request->validate([
            'repartidor_id' => [
                'required',
                'exists:usuarios,id',
                Rule::exists('usuarios', 'id')->where(function ($query) {
                    $query->where('rol', 'repartidor');
                }),
                function ($attribute, $value, $fail) {
                    $repartidoresDisponiblesIds = $this->getAvailableRepartidores()->pluck('id')->toArray();
                    if (!in_array($value, $repartidoresDisponiblesIds)) {
                        $fail('El repartidor seleccionado ya tiene un pedido asignado o en ruta.');
                    }
                },
            ],
        ]);

        if ($pedido->envio && $pedido->envio->repartidor_id) {
            return redirect()->back()->with('error', 'Este pedido ya tiene un repartidor asignado.');
        }

        DB::transaction(function () use ($request, $pedido) {
            $envio = Envio::where('pedido_id', $pedido->id)->first();

            if (!$envio) {
                // Esto no debería ocurrir si el pedido se crea con un registro de envío.
                // Pero es una buena salvaguarda.
                $envio = Envio::create([
                    'pedido_id' => $pedido->id,
                    'estado' => 'asignado',
                    'actualizado_en' => now()
                ]);
            }

            $envio->repartidor_id = $request->repartidor_id;
            $envio->estado = 'en ruta'; // El estado del ENVIO cambia a 'en ruta'
            $envio->actualizado_en = now();
            $envio->save();

            // Esto es lo que necesitas para cambiar el estado del PEDIDO
            $pedido->estado = 'en camino'; // <-- ¡Ahora el estado del PEDIDO cambia a 'en camino'!
            $pedido->save();
        });

        return redirect()->route('admin.pedidos.index')->with('success', 'Repartidor asignado exitosamente al pedido #' . $pedido->id);
    }

    public function detalles(Pedido $pedido)
    {
        // Carga todas las relaciones necesarias para mostrar la información completa del pedido
        $pedido->load([
            'usuario',              // Quién hizo el pedido
            'envio.repartidor',     // Información de envío y repartidor asignado
            'pago',                 // Información del pago
            'items.producto',       // Detalles de los productos en el pedido
            'items.promocion.detalles.producto' // Detalles de las promociones y sus productos
        ]);

        return view('admin.pedidos.detalles', compact('pedido'));
    }


    
}
