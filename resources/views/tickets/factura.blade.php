<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .factura-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .client-info {
            clear: both;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f9fa;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px;
        }
        .totals td:last-child {
            text-align: right;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>PosPrime</h2>
            <p>Calle Magnolia Nº5</p>
            <p>NIF/CIF: 12345678A</p>
            <p>Teléfono: +34 900 000 000</p>
            <p>Email: info@posprime.com</p>
        </div>
        <div class="factura-info">
            <h1>FACTURA</h1>
            <p>Factura Nº: {{ $numero_ticket }}</p>
            <p>Fecha: {{ $fecha }}</p>
            <p>Forma de pago: Efectivo</p>
        </div>
    </div>

    <div class="client-info">
        <h3>DATOS DEL CLIENTE</h3>
        @if(isset($cliente) && $cliente)
    <p>Razón Social: {{ $cliente->persona->razon_social }}</p>
    <p>{{ $cliente->persona->documento->tipo_documento }}: {{ $cliente->persona->numero_documento }}</p>
    <p>Dirección: {{ $cliente->persona->direccion }}</p>
@else
    <p>Cliente General</p>
@endif
    </div>

    <table class="items-table">
    <thead>
        <tr>
            <th>DESCRIPCIÓN</th>
            <th>CANTIDAD</th>
            <th>PRECIO UNITARIO</th>
            <th>DESCUENTO %</th>
            <th>IMPORTE DTO.</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($productos as $producto)
        @php
            $subtotalProducto = $producto['cantidad'] * $producto['precio_venta'];
            $importeDescuento = ($subtotalProducto * $producto['descuento']) / 100;
            $totalConDescuento = $subtotalProducto - $importeDescuento;
        @endphp
        <tr>
            <td>{{ $producto['nombre'] }}</td>
            <td>{{ $producto['cantidad'] }}</td>
            <td>{{ number_format($producto['precio_venta'], 2) }}€</td>
            <td>{{ number_format($producto['descuento'], 2) }}%</td>
            <td>{{ number_format($importeDescuento, 2) }}€</td>
            <td>{{ number_format($totalConDescuento, 2) }}€</td>
        </tr>
        @endforeach
    </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>{{ number_format($subtotal, 2) }}€</td>
            </tr>
            <tr>
                <td>IVA (21%):</td>
                <td>{{ number_format($iva, 2) }}€</td>
            </tr>
            <tr style="font-weight: bold;">
                <td>TOTAL:</td>
                <td>{{ number_format($total, 2) }}€</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>GRACIAS POR SU COMPRA</p>
        <p>Este documento sirve como justificante legal de su compra.</p>
        <p>Para cualquier reclamación conserve esta factura.</p>
    </div>
</body>
</html>