<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 300px;
            padding: 20px;
            border: 1px solid #000;
            margin: 0 auto;
        }
        .header, .footer {
            text-align: center;
        }
        .details, .items, .totals {
            margin-bottom: 20px;
        }
        .items th, .items td {
            text-align: left;
            padding: 5px;
        }
        .totals th, .totals td {
            text-align: right;
            padding: 5px;
        }
        .totals {
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h2>PosPrime</h2>
            <p>Calle Magnolia Nº5</p>
            <p>NIF/CIF: 12345678A</p>
            <p>Fecha: {{ $fecha }}</p>
            <p>Ticket No: {{ $numero_ticket }}</p>
        </div>
        <div class="details">
    <p>Cliente: {{ isset($cliente) && $cliente && isset($cliente->persona) ? $cliente->persona->razon_social : 'Cliente General' }}</p>
</div>
        <div class="items">
            <table width="100%">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Dto.%</th>
                        <th>Imp.Dto.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
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
        </div>
        <div class="totals">
            <table width="100%">
                <tr>
                    <th>Subtotal:</th>
                    <td>{{ number_format($subtotal ?? 0, 2) }} €</td>
                </tr>
                <tr>
                    <th>IVA (21%):</th>
                    <td>{{ number_format($iva ?? 0, 2) }} €</td>
                </tr>
                <tr>
                    <th>Total:</th>
                    <td>{{ number_format($total ?? 0, 2) }} €</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>¡Gracias por su compra!</p>
        </div>
    </div>
</body>
</html>