<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Cálculos existentes para las tarjetas resumen
        $ordenesHoy = Pedido::whereDate('fecha', today())->count();
        $gananciasHoy = Pedido::whereDate('fecha', today())->sum('total');
        $totalOrdenes = Pedido::count();

        // Datos del gráfico de ventas de los últimos 7 días (mantenemos tu implementación)
        $dias = [];
        $ventas = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            $dias[] = $fecha->translatedFormat('D'); // Ej: Lun, Mar
            $ventas[] = round(Pedido::whereDate('fecha', $fecha)->sum('total'), 2);
        }

        // Datos del gráfico de ventas mensuales (Últimos 12 meses, dinámico y con año)
        // Esto es más robusto que tener un array fijo de meses para el JS.
        $mesesChartLabels = []; // Nombres de los meses para el JS
        $ventasPorMesChartData = []; // Valores de ventas para el JS
        for ($i = 11; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $mesesChartLabels[] = $mes->translatedFormat('M Y'); // Ej: 'Ene 2024', 'Feb 2024'
            $ventasPorMesChartData[] = Pedido::whereYear('fecha', $mes->year)
                                            ->whereMonth('fecha', $mes->month)
                                            ->sum('total');
        }

        // Órdenes por estado
        $estadosPedidos = Pedido::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $labelsEstado = $estadosPedidos->keys();
        $valoresEstado = $estadosPedidos->values();

        // --- Nuevas Métricas Adicionales para el Dashboard ---

        // Total de Usuarios Registrados
        $totalUsuarios = Usuario::count();

        // Total de Productos en el catálogo
        $totalProductos = Producto::count();

        // Últimos 5 Pedidos Recientes (para una tabla en el dashboard)
        // Carga la relación 'usuario' para mostrar el nombre del cliente
        $ultimosPedidos = Pedido::with('usuario')
                                ->orderBy('created_at', 'desc') // Ordena por fecha de creación del pedido
                                ->take(5) // Limita a los 5 más recientes
                                ->get();


        return view('admin.dashboard', compact(
            'ordenesHoy',
            'gananciasHoy',
            'totalOrdenes',
            'dias',                 // Para el gráfico de 7 días (si lo usas)
            'ventas',               // Para el gráfico de 7 días (si lo usas)
            'mesesChartLabels',     // Para el gráfico de ventas mensuales
            'ventasPorMesChartData',// Para el gráfico de ventas mensuales
            'labelsEstado',
            'valoresEstado',
            'totalUsuarios',        // Nuevo
            'totalProductos',       // Nuevo
            'ultimosPedidos'        // Nuevo
        ));
    }

}