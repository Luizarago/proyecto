<!DOCTYPE html>
<html lang="es">

<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        @page {
            size: 3.5in 6in;
            margin: 0.2in;
        }

        table {
            width: 100%;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        #logo {
            width: 60%;
            text-align: center;
            margin: 0 auto 10px auto;
            display: block;
        }

        header {
            text-align: center;
            margin-bottom: 10px;
        }

        .items thead {
            text-align: center;
        }

        .center-align {
            text-align: center;
        }

        .bill-details td {
            font-size: 12px;
        }

        .receipt {
            font-size: medium;
        }

        .items .heading {
            font-size: 12.5px;
            text-transform: uppercase;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            padding: 5px 0;
        }

        .items td {
            font-size: 12px;
            text-align: right;
            padding: 5px 0;
        }

        .sum-up {
            text-align: right !important;
        }

        .total {
            font-size: 13px;
            border-top: 1px dashed black !important;
            border-bottom: 1px dashed black !important;
            padding: 5px 0;
        }

        .line {
            border-top: 1px solid black !important;
        }

        p {
            padding: 1px;
            margin: 0;
            margin: 5px 0;
        }

        section,
        footer {
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <header>
        <h1>PosPrime</h1>
        <p>Calle Magnolia Nº5</p>
        <p>NIF/CIF: 12345678A</p>
    </header>
    <table class="bill-details">
        <tbody>
            <tr>
                <td>Fecha: <span>{{ $fecha }}</span></td>
                <td>Ticket Nº: <span>{{ $numero_ticket }}</span></td>
            </tr>
            <tr>
                <td colspan="2">Cliente: <span>{{ isset($cliente) && $cliente && isset($cliente->persona) ? $cliente->persona->razon_social : 'Cliente General' }}</span></td>
            </tr>
        </tbody>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="heading name">Producto</th>
                <th class="heading qty">Cant.</th>
                <th class="heading rate">Precio</th>
                <th class="heading amount">Total</th>
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
                <td>{{ number_format($totalConDescuento, 2) }}€</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="sum-up line">Subtotal</td>
                <td class="line">{{ number_format($subtotal ?? 0, 2) }}€</td>
            </tr>
            <tr>
                <td colspan="3" class="sum-up">IVA (21%)</td>
                <td>{{ number_format($iva ?? 0, 2) }}€</td>
            </tr>
            <tr>
                <th colspan="3" class="total">Total</th>
                <th class="total">{{ number_format($total ?? 0, 2) }}€</th>
            </tr>
        </tbody>
    </table>
    <section>
        <p style="text-align:center">
            ¡Gracias por su compra!
        </p>
    </section>
    <footer style="text-align:center">
        <p>www.posprime.com</p>
    </footer>
</body>

</html>