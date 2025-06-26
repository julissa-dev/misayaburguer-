<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem; // Asegúrate de importar PedidoItem
use App\Models\Producto;   // Asegúrate de importar Producto
use App\Models\Promocion;  // Asegúrate de importar Promocion
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Genera un reporte de ventas por rango de fechas, incluyendo productos y promociones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generarVentas(Request $request)
    {
        // 1. Obtener y validar las fechas del request
        $fechaDesde = Carbon::parse($request->input('fecha_desde', Carbon::now()->subDays(30)->format('Y-m-d')))->startOfDay();
        $fechaHasta = Carbon::parse($request->input('fecha_hasta', Carbon::now()->format('Y-m-d')))->endOfDay();

        // Asegurarse de que la fecha de inicio no sea posterior a la fecha de fin
        if ($fechaDesde->greaterThan($fechaHasta)) {
            return redirect()->back()->with('error', 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".');
        }

        // 2. Realizar las consultas a la base de datos

        // Métricas generales de ventas
        $pedidosEnRango = Pedido::whereBetween('fecha', [$fechaDesde, $fechaHasta])
                                ->get();

        $totalVentasRango = $pedidosEnRango->sum('total');
        $cantidadPedidosRango = $pedidosEnRango->count();

        // Productos más vendidos en el rango de fechas
        $productosMasVendidos = PedidoItem::select(
                                    'producto_id',
                                    DB::raw('SUM(cantidad) as total_cantidad_vendida'),
                                    DB::raw('SUM(precio_unit * cantidad) as total_ingreso_producto')
                                )
                                ->whereNotNull('producto_id') // Solo para items que son productos, no promociones completas
                                ->whereHas('pedido', function ($query) use ($fechaDesde, $fechaHasta) {
                                    $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
                                })
                                ->with('producto') // Cargar la relación para obtener detalles del producto
                                ->groupBy('producto_id')
                                ->orderByDesc('total_cantidad_vendida')
                                ->take(10) // Top 10 productos
                                ->get();

        // Promociones más utilizadas en el rango de fechas
        $promocionesMasUtilizadas = PedidoItem::select(
                                    'promocion_id',
                                    DB::raw('COUNT(DISTINCT pedido_id) as total_pedidos_con_promocion'), // Cuántos pedidos usaron esta promo
                                    DB::raw('SUM(cantidad) as total_cantidad_promocional'), // Cuántos items promocionales se vendieron
                                    DB::raw('SUM(precio_unit * cantidad) as total_ingreso_promocional') // Ingreso si la promo tiene precio_unit
                                )
                                ->whereNotNull('promocion_id') // Solo para items que son promociones
                                ->whereHas('pedido', function ($query) use ($fechaDesde, $fechaHasta) {
                                    $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
                                })
                                ->with('promocion') // Cargar la relación para obtener detalles de la promoción
                                ->groupBy('promocion_id')
                                ->orderByDesc('total_pedidos_con_promocion')
                                ->take(10) // Top 10 promociones
                                ->get();

        // 3. Pasar los datos a la vista
        return view('admin.reportes.ventas_por_fecha', compact(
            'fechaDesde',
            'fechaHasta',
            'totalVentasRango',
            'cantidadPedidosRango',
            'productosMasVendidos',     // Nuevo
            'promocionesMasUtilizadas'  // Nuevo
        ));
    }
}