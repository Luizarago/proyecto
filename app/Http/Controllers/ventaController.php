<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use Illuminate\Support\Str;

class VentaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
    }

    public function index()
    {
        $ventas = Venta::with(['comprobante', 'cliente.persona', 'user'])
            ->where('estado', 1)
            ->latest()
            ->get();

        return view('venta.index', compact('ventas'));
    }

    public function create()
    {
        $subquery = DB::table('compra_producto')
            ->select('producto_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('producto_id');

        $productos = Producto::join('compra_producto as cpr', function ($join) use ($subquery) {
            $join->on('cpr.producto_id', '=', 'productos.id')
                ->whereIn('cpr.created_at', function ($query) use ($subquery) {
                    $query->select('max_created_at')
                        ->fromSub($subquery, 'subquery')
                        ->whereRaw('subquery.producto_id = cpr.producto_id');
                });
        })
            ->select('productos.nombre', 'productos.id', 'productos.stock', 'cpr.precio_venta')
            ->where('productos.estado', 1)
            ->where('productos.stock', '>', 0)
            ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        $comprobantes = Comprobante::all();

        return view('venta.create', compact('productos', 'clientes', 'comprobantes'));
    }

        
    public function generarTicket(Request $request)
{
    if (!$request->has('productos')) {
        return response()->json(['error' => 'No se enviaron productos'], 400);
    }

    $productosArray = json_decode($request->input('productos'), true);

    if (!is_array($productosArray)) {
        return response()->json(['error' => 'Formato de productos inválido'], 400);
    }
    
    Log::info('Productos recibidos:', ['productos' => $productosArray]);

    $productosVenta = [];
    $subtotal = 0;

    foreach ($productosArray as $producto) {
        $productId = $producto['producto_id'] ?? $producto['id'] ?? null;
        if ($productId === null) {
            Log::warning('Producto sin identificador', ['producto' => $producto]);
            continue;
        }

        $productoDetails = Producto::find($productId);
        if (!$productoDetails) {
            Log::warning('Producto no encontrado en BD', ['productId' => $productId]);
            continue;
        }

        $precioVenta = $producto['precio_venta'] ?? $producto['precio'] ?? 0;
        $cantidad = $producto['cantidad'] ?? 0;
        $porcentajeDescuento = $producto['descuento'] ?? 0;

        // Calcular descuento como porcentaje
        $subtotalProducto = $cantidad * $precioVenta;
        $importeDescuento = ($subtotalProducto * $porcentajeDescuento) / 100;
        $totalProducto = $subtotalProducto - $importeDescuento;

        $productosVenta[] = [
            'producto_id' => $productId,
            'nombre' => $productoDetails->nombre,
            'cantidad' => $cantidad,
            'precio_venta' => $precioVenta,
            'descuento' => $porcentajeDescuento,
            'importe_descuento' => $importeDescuento,
            'total' => $totalProducto
        ];

        $subtotal += $totalProducto;
    }

    $clienteId = $request->input('cliente_id');
    $cliente = $clienteId ? Cliente::find($clienteId) : null;

    $data = [
        'productos' => $productosVenta,
        'subtotal' => $subtotal,
        'iva' => $subtotal * 0.21,
        'total' => $subtotal + ($subtotal * 0.21),
        'numero_ticket' => $request->input('numero_ticket') ?? 'N/A',
        'fecha' => now()->format('d-m-Y'),
        'cliente' => $cliente
    ];

    Log::info('Datos para generar PDF:', $data);

    $comprobanteId = $request->input('comprobante_id');
    $vista = $comprobanteId == 2 ? 'tickets.factura' : 'tickets.ticket';

    $pdf = PDF::loadView($vista, $data);
    return $pdf->download("ticket_{$data['numero_ticket']}.pdf");
}
public function store(StoreVentaRequest $request)
{
    try {
        DB::beginTransaction();

        $ventaData = $request->validated();
        $ventaData['numero_ticket'] = $this->generateSequentialTicketNumber();
        
        $isFactura = $request->input('comprobante_id') == 2;
        if ($isFactura) {
            $ventaData['numero_comprobante'] = $this->generateSequentialInvoiceNumber();
        }
        
        $venta = Venta::create($ventaData);

        $productosVenta = [];
        $subtotal = 0;

        foreach ($request->get('arrayidproducto') as $i => $productoId) {
            $producto = Producto::find($productoId);
            $cantidad = $request->get('arraycantidad')[$i];

            if (!$producto || $producto->stock < $cantidad) {
                throw new Exception("Stock insuficiente para el producto: {$producto->nombre}");
            }

            $precioVenta = $request->get('arrayprecioventa')[$i];
            $porcentajeDescuento = $request->get('arraydescuento')[$i];

            $subtotalProducto = $cantidad * $precioVenta;
            $importeDescuento = ($subtotalProducto * $porcentajeDescuento) / 100;
            $totalProducto = $subtotalProducto - $importeDescuento;

            $productosVenta[] = [
                'nombre' => $producto->nombre,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_venta' => $precioVenta,
                'descuento' => $porcentajeDescuento,
                'importe_descuento' => $importeDescuento,
                'total' => $totalProducto
            ];

            $subtotal += $totalProducto;
            $producto->update(['stock' => $producto->stock - $cantidad]);
        }

        $venta->productos()->syncWithoutDetaching(
            collect($productosVenta)->mapWithKeys(function ($item) {
                return [$item['producto_id'] => [
                    'cantidad' => $item['cantidad'],
                    'precio_venta' => $item['precio_venta'],
                    'descuento' => $item['descuento']
                ]];
            })->toArray()
        );

        $iva = $subtotal * 0.21;
        $total = $subtotal + $iva;

        $cliente = $request->input('cliente_id') ? 
            Cliente::with('persona')->find($request->input('cliente_id')) : 
            null;

        $data = [
            'numero_ticket' => $venta->numero_ticket,
            'productos' => $productosVenta,
            'comprobante_id' => $request->input('comprobante_id'),
            'cliente' => $cliente,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'fecha' => $request->input('fecha') ?? now()->format('d-m-Y'),
            'empresa' => [
                'nombre' => 'PosPrime',
                'direccion' => 'Calle Magnolia Nº5',
                'nif' => '12345678A',
                'telefono' => '+34 900 000 000',
                'email' => 'info@posprime.com'
            ]
        ];

        // Definir rutas y nombre del archivo
        $storagePath = storage_path('app/public');
        $ticketsPath = $storagePath . '/tickets';
        $vista = $isFactura ? 'tickets.factura' : 'tickets.ticket';
        $fileName = $isFactura ? 
            "factura_{$venta->numero_ticket}.pdf" : 
            "ticket_{$venta->numero_ticket}.pdf";
        $fullPath = $ticketsPath . '/' . $fileName;

        // Crear directorios si no existen
        foreach ([$storagePath, $ticketsPath] as $path) {
            if (!file_exists($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new Exception("No se pudo crear el directorio: {$path}");
                }
            }
        }

        // Generar y guardar PDF
        try {
            $pdf = PDF::loadView($vista, $data);
            $pdf->save($fullPath);

            // Verificar que el archivo se creó y es legible
            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                throw new Exception("Error al crear o leer el archivo PDF");
            }

            // Establecer permisos correctos
            chmod($fullPath, 0644);

        } catch (Exception $e) {
            throw new Exception("Error al generar el PDF: " . $e->getMessage());
        }

        // Registrar información en el log
        Log::info('PDF creado exitosamente', [
            'nombre_archivo' => $fileName,
            'ruta' => $fullPath,
            'tamaño' => filesize($fullPath),
            'permisos' => substr(sprintf('%o', fileperms($fullPath)), -4)
        ]);

        DB::commit();

        // Retornar con la URL pública
        return redirect()->route('ventas.index')
            ->with('success', $isFactura ? 'Factura generada correctamente' : 'Ticket generado correctamente')
            ->with('pdf_url', asset("storage/tickets/{$fileName}"));

    } catch (Exception $e) {
        DB::rollBack();
        Log::error("Error en la venta: " . $e->getMessage());
        Log::error("Traza: " . $e->getTraceAsString());
        return redirect()->route('ventas.index')
            ->with('error', 'Error en la venta: ' . $e->getMessage());
    }
}

private function generateSequentialTicketNumber()
{
    // Obtener el último número de ticket
    $lastTicket = Venta::orderBy('numero_ticket', 'desc')->first();
    $newTicketNumber = $lastTicket ? $lastTicket->numero_ticket + 1 : 1;

    Log::info('Nuevo número de ticket generado:', ['numero_ticket' => $newTicketNumber]);

    return $newTicketNumber;
}
private function generateSequentialInvoiceNumber()
{
    // Obtener el último número de factura
    $lastInvoice = Venta::where('comprobante_id', 2)
        ->orderBy('numero_comprobante', 'desc')
        ->first();
    
    $year = date('Y');
    $lastNumber = $lastInvoice ? 
        (int)substr($lastInvoice->numero_comprobante, -5) : 0;
    
    $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    return "F-{$year}-{$newNumber}";
}


    public function show(Venta $venta)
    {
        return view('venta.show', compact('venta'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        Venta::where('id', $id)->update(['estado' => 0]);

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada');
    }
}