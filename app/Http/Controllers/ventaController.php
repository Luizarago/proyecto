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
        // Subconsulta: obtiene el ID de cada producto junto con la fecha de su última compra
        $subquery = DB::table('compra_producto')
            ->select('producto_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('producto_id');
    
        // Consulta principal: obtiene los productos activos y con stock,
        // junto con el precio de venta de su última compra registrada
        $productos = Producto::join('compra_producto as cpr', function ($join) use ($subquery) {
            $join->on('cpr.producto_id', '=', 'productos.id')
                // Solo une el registro de compra más reciente para cada producto
                ->whereIn('cpr.created_at', function ($query) use ($subquery) {
                    $query->select('max_created_at')
                        ->fromSub($subquery, 'subquery')
                        ->whereRaw('subquery.producto_id = cpr.producto_id');
                });
        })
            ->select('productos.nombre', 'productos.id', 'productos.stock', 'cpr.precio_venta')
            ->where('productos.estado', 1)      // Solo productos activos
            ->where('productos.stock', '>', 0)  // Solo productos con stock disponible
            ->get();
    
        // Obtiene los clientes que tienen una persona asociada activa
        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
    
        // Obtiene todos los tipos de comprobantes (factura, ticket, etc.)
        $comprobantes = Comprobante::all();
    
        // Retorna la vista para crear una venta, enviando los productos, clientes y comprobantes
        return view('venta.create', compact('productos', 'clientes', 'comprobantes'));
    }

        
        public function generarTicket(Request $request)
    {
        // Verifica que la petición incluya productos
        if (!$request->has('productos')) {
            return response()->json(['error' => 'No se enviaron productos'], 400);
        }
    
        // Decodifica el JSON de productos recibido
        $productosArray = json_decode($request->input('productos'), true);
    
        // Valida que el formato de productos sea un array
        if (!is_array($productosArray)) {
            return response()->json(['error' => 'Formato de productos inválido'], 400);
        }
        
        // Registra en el log los productos recibidos
        Log::info('Productos recibidos:', ['productos' => $productosArray]);
    
        $productosVenta = [];
        $subtotal = 0;
    
        // Recorre cada producto para calcular totales y descuentos
        foreach ($productosArray as $producto) {
            // Obtiene el ID del producto (puede venir como 'producto_id' o 'id')
            $productId = $producto['producto_id'] ?? $producto['id'] ?? null;
            if ($productId === null) {
                Log::warning('Producto sin identificador', ['producto' => $producto]);
                continue;
            }
    
            // Busca el producto en la base de datos
            $productoDetails = Producto::find($productId);
            if (!$productoDetails) {
                Log::warning('Producto no encontrado en BD', ['productId' => $productId]);
                continue;
            }
    
            // Obtiene precio, cantidad y descuento del producto
            $precioVenta = $producto['precio_venta'] ?? $producto['precio'] ?? 0;
            $cantidad = $producto['cantidad'] ?? 0;
            $porcentajeDescuento = $producto['descuento'] ?? 0;
    
            // Calcula el subtotal, descuento e importe total del producto
            $subtotalProducto = $cantidad * $precioVenta;
            $importeDescuento = ($subtotalProducto * $porcentajeDescuento) / 100;
            $totalProducto = $subtotalProducto - $importeDescuento;
    
            // Agrega los datos del producto al array de productos de la venta
            $productosVenta[] = [
                'producto_id' => $productId,
                'nombre' => $productoDetails->nombre,
                'cantidad' => $cantidad,
                'precio_venta' => $precioVenta,
                'descuento' => $porcentajeDescuento,
                'importe_descuento' => $importeDescuento,
                'total' => $totalProducto
            ];
    
            // Suma el total del producto al subtotal general
            $subtotal += $totalProducto;
        }
    
        // Busca el cliente si se envió un ID de cliente
        $clienteId = $request->input('cliente_id');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;
    
        // Prepara los datos para la vista del ticket/factura
        $data = [
            'productos' => $productosVenta,
            'subtotal' => $subtotal,
            'iva' => $subtotal * 0.21,
            'total' => $subtotal + ($subtotal * 0.21),
            'numero_ticket' => $request->input('numero_ticket') ?? 'N/A',
            'fecha' => now()->format('d-m-Y'),
            'cliente' => $cliente
        ];
    
        // Registra en el log los datos que se usarán para el PDF
        Log::info('Datos para generar PDF:', $data);
    
        // Determina la vista a usar según el tipo de comprobante (factura o ticket)
        $comprobanteId = $request->input('comprobante_id');
        $vista = $comprobanteId == 2 ? 'tickets.factura' : 'tickets.ticket';
    
        // Genera el PDF y lo descarga
        $pdf = PDF::loadView($vista, $data);
        return $pdf->download("ticket_{$data['numero_ticket']}.pdf");
    }
public function store(StoreVentaRequest $request)
{
    try {
        // Inicia una transacción de base de datos
        DB::beginTransaction();

        // Valida y recoge los datos de la venta
        $ventaData = $request->validated();
        // Genera un número de ticket secuencial
        $ventaData['numero_ticket'] = $this->generateSequentialTicketNumber();
        
        // Si es factura, genera también el número de factura secuencial
        $isFactura = $request->input('comprobante_id') == 2;
        if ($isFactura) {
            $ventaData['numero_comprobante'] = $this->generateSequentialInvoiceNumber();
        }
        
        // Crea la venta en la base de datos
        $venta = Venta::create($ventaData);

        $productosVenta = [];
        $subtotal = 0;

        // Recorre los productos enviados en la venta
        foreach ($request->get('arrayidproducto') as $i => $productoId) {
            $producto = Producto::find($productoId);
            $cantidad = $request->get('arraycantidad')[$i];

            // Verifica que haya suficiente stock
            if (!$producto || $producto->stock < $cantidad) {
                throw new Exception("Stock insuficiente para el producto: {$producto->nombre}");
            }

            $precioVenta = $request->get('arrayprecioventa')[$i];
            $porcentajeDescuento = $request->get('arraydescuento')[$i];

            // Calcula subtotal, descuento e importe total del producto
            $subtotalProducto = $cantidad * $precioVenta;
            $importeDescuento = ($subtotalProducto * $porcentajeDescuento) / 100;
            $totalProducto = $subtotalProducto - $importeDescuento;

            // Agrega los datos del producto al array de productos de la venta
            $productosVenta[] = [
                'nombre' => $producto->nombre,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_venta' => $precioVenta,
                'descuento' => $porcentajeDescuento,
                'importe_descuento' => $importeDescuento,
                'total' => $totalProducto
            ];

            // Suma el total del producto al subtotal general
            $subtotal += $totalProducto;
            // Actualiza el stock del producto
            $producto->update(['stock' => $producto->stock - $cantidad]);
        }

        // Sincroniza los productos con la venta en la tabla pivote
        $venta->productos()->syncWithoutDetaching(
            collect($productosVenta)->mapWithKeys(function ($item) {
                return [$item['producto_id'] => [
                    'cantidad' => $item['cantidad'],
                    'precio_venta' => $item['precio_venta'],
                    'descuento' => $item['descuento']
                ]];
            })->toArray()
        );

        // Calcula IVA y total
        $iva = $subtotal * 0.21;
        $total = $subtotal + $iva;

        // Busca el cliente con su persona asociada si se envió un ID
        $cliente = $request->input('cliente_id') ? 
            Cliente::with('persona')->find($request->input('cliente_id')) : 
            null;

        // Prepara los datos para el PDF
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

        // Define rutas y nombre del archivo PDF
        $storagePath = storage_path('app/public');
        $ticketsPath = $storagePath . '/tickets';
        $vista = $isFactura ? 'tickets.factura' : 'tickets.ticket';
        $fileName = $isFactura ? 
            "factura_{$venta->numero_ticket}.pdf" : 
            "ticket_{$venta->numero_ticket}.pdf";
        $fullPath = $ticketsPath . '/' . $fileName;

        // Crea los directorios si no existen
        foreach ([$storagePath, $ticketsPath] as $path) {
            if (!file_exists($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new Exception("No se pudo crear el directorio: {$path}");
                }
            }
        }

        // Genera y guarda el PDF en disco
        try {
            $pdf = PDF::loadView($vista, $data);
            $pdf->save($fullPath);

            // Verifica que el archivo se creó y es legible
            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                throw new Exception("Error al crear o leer el archivo PDF");
            }

            // Establece permisos correctos al archivo
            chmod($fullPath, 0644);

        } catch (Exception $e) {
            throw new Exception("Error al generar el PDF: " . $e->getMessage());
        }

        // Registra información en el log
        Log::info('PDF creado exitosamente', [
            'nombre_archivo' => $fileName,
            'ruta' => $fullPath,
            'tamaño' => filesize($fullPath),
            'permisos' => substr(sprintf('%o', fileperms($fullPath)), -4)
        ]);

        // Confirma la transacción
        DB::commit();

        // Redirige con mensaje de éxito y la URL pública del PDF
        return redirect()->route('ventas.index')
            ->with('success', $isFactura ? 'Factura generada correctamente' : 'Ticket generado correctamente')
            ->with('pdf_url', asset("storage/tickets/{$fileName}"));

    } catch (Exception $e) {
        // Si hay error, revierte la transacción y muestra mensaje de error
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

    Log::info('Nuevo nÃºmero de ticket generado:', ['numero_ticket' => $newTicketNumber]);

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