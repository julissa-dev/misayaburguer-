@extends('layouts.admin')

@section('titulo', 'Dashboard')

@section('contenido')
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Bienvenido, {{ Auth::user()->nombre }}</h2>

    {{-- Tarjetas resumen --}}
    {{-- Se ajusta el grid para acomodar más tarjetas en pantallas grandes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Órdenes de Hoy</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $ordenesHoy }}</h3>
        </div>

        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Ganancias de Hoy</p>
            <h3 class="text-3xl font-bold text-green-600 mt-2">S/ {{ number_format($gananciasHoy, 2) }}</h3>
        </div>

        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Órdenes Totales</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $totalOrdenes }}</h3>
        </div>

        {{-- Nuevas Tarjetas para Usuarios y Productos --}}
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Total Usuarios</p>
            <h3 class="text-3xl font-bold text-purple-600 mt-2">{{ $totalUsuarios }}</h3>
        </div>

        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">Total Productos</p>
            <h3 class="text-3xl font-bold text-orange-600 mt-2">{{ $totalProductos }}</h3>
        </div>
    </div>

    {{-- Formulario de reporte --}}
    <div class="bg-white rounded-lg shadow p-6 mb-10">
        <h4 class="text-lg font-semibold text-gray-700 mb-4">Reporte de Ventas por Rango de Fechas</h4>
        {{-- Se añade un action y method para que el formulario sea funcional --}}
        <form action="{{ route('admin.reportes.generar') }}" method="GET"
            class="flex flex-col md:flex-row gap-4 items-center">
            {{-- CSRF token no es estrictamente necesario para GET, pero si lo cambias a POST, sí --}}
            {{-- @csrf --}}
            <div class="w-full md:w-auto">
                <label for="fecha_desde" class="block text-sm text-gray-600 mb-1">Desde:</label>
                <input type="date" id="fecha_desde" name="fecha_desde"
                    value="{{ request('fecha_desde', \Carbon\Carbon::now()->subDays(30)->format('Y-m-d')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div class="w-full md:w-auto">
                <label for="fecha_hasta" class="block text-sm text-gray-600 mb-1">Hasta:</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta"
                    value="{{ request('fecha_hasta', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                    class="w-full px-3 py   -2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <button type="submit"
                class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200 mt-2 md:mt-0">
                Generar Reporte
            </button>
        </form>
    </div>

    {{-- Sección de Gráficos --}}
    <div class="flex flex-col lg:flex-row gap-6 mb-8">
        {{-- Gráfico de Ventas Mensuales --}}
        <div class="bg-white rounded-lg shadow p-6 w-full lg:w-2/3 h-96">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold text-gray-700">Ventas por Mes</h4>
                <span class="text-sm text-gray-500">Últimos 12 meses</span>
            </div>
            <canvas id="ventasAreaChart" class="w-full h-full"></canvas>
        </div>

        {{-- Gráfico de Estados de Pedidos --}}
        <div class="bg-white rounded-lg shadow p-6 w-full lg:w-1/3 h-96 flex flex-col justify-between">
            <div>
                <h4 class="text-lg font-semibold text-gray-700 mb-4 text-center">Estados de Pedidos</h4>
                <div class="flex justify-center items-center h-48">
                    <canvas id="donutEstados" class="w-full max-w-xs"></canvas>
                </div>
            </div>
            <div class="mt-2 text-center text-sm text-gray-500">
                Total: {{ $totalOrdenes }} pedidos
            </div>
        </div>
    </div>

    {{-- Sección de Últimos Pedidos Recientes --}}
    <div class="bg-white rounded-lg shadow p-6 mb-10">
        <h4 class="text-lg font-semibold text-gray-700 mb-4">Últimos Pedidos Recientes</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ID Pedido
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Total
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Estado
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimosPedidos as $pedido)
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">#{{ $pedido->id }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{-- Asegúrate que el modelo Pedido tenga la relación 'usuario' definida --}}
                                <p class="text-gray-900 whitespace-no-wrap">{{ $pedido->usuario->nombre ?? 'N/A' }}
                                    {{ $pedido->usuario->apellido ?? '' }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">S/ {{ number_format($pedido->total, 2) }}</p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{-- Estilos para el estado del pedido --}}
                                <span
                                    class="relative inline-block px-3 py-1 font-semibold leading-tight
                                    @if ($pedido->estado == 'pendiente') text-yellow-900 bg-yellow-200 rounded-full @endif
                                    @if ($pedido->estado == 'en proceso') text-blue-900 bg-blue-200 rounded-full @endif
                                    @if ($pedido->estado == 'completado') text-green-900 bg-green-200 rounded-full @endif
                                    @if ($pedido->estado == 'cancelado') text-red-900 bg-red-200 rounded-full @endif
                                ">
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">{{ $pedido->created_at->format('d/m/Y H:i') }}
                                </p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                {{-- Enlace a la vista de detalles del pedido, asume que puedes filtrar por ID o tienes una vista 'show' --}}
                                <a href="{{ route('admin.pedidos.index', ['search_id' => $pedido->id]) }}"
                                    class="text-indigo-600 hover:text-indigo-900" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                No hay pedidos recientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script para los gráficos --}}
    <script>
        // Configuración del gráfico de ventas mensuales (ahora usa $mesesChartLabels y $ventasPorMesChartData)
        const initVentasChart = () => {
            const ctx = document.getElementById('ventasAreaChart').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
            gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($mesesChartLabels), // Usar la nueva variable
                    datasets: [{
                        label: 'Ventas por mes',
                        data: @json($ventasPorMesChartData), // Usar la nueva variable
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'white',
                        pointBorderColor: 'rgba(54, 162, 235, 1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'S/ ' + ctx.formattedValue,
                                title: items => items[0].label
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 400,
                            grid: {
                                display: false
                            },
                            ticks: {
                                callback: val => 'S/ ' + val,
                                font: {
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    }
                }
            });
        };

        // Configuración del gráfico de estados (sin cambios aquí, ya usa tus variables)
        const initEstadosChart = () => {
            const ctx = document.getElementById('donutEstados').getContext('2d');

            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($labelsEstado),
                    datasets: [{
                        label: 'Pedidos por estado',
                        data: @json($valoresEstado),
                        backgroundColor: [
                            '#facc15', // Pendientes
                            '#60a5fa', // En proceso
                            '#34d399', // Completados
                            '#ef4444', // Puedes añadir un color para 'cancelado' si es un estado posible
                            // Añade más colores si tienes más estados de pedido en tu DB
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#4b5563',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 14
                                },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw}`
                            }
                        }
                    }
                }
            });
        };

        // Inicializar gráficos cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            initVentasChart();
            initEstadosChart();
        });
    </script>
@endsection
