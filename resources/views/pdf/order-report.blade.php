<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: orange;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .footer {
            background-color: #007BFF;
            color: white;
            text-align: center;
            font-size: 12px;
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 10px 0;
        }
        .content {
            padding: 20px;
            margin-bottom: 60px; /* to avoid overlapping the footer */
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .sign {
            width: 45%;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ $title }}</strong>
    </div>

    <div class="content">
        <div class="info">
            <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p> <!-- You can adjust the date here -->
            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            <p><strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}</p>
            <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Variant</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ json_decode($item->attributes)->Material ?? '-' }}</td> <!-- Get the attribute value -->
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-section">
            <div class="sign">Seller Signature</div>
            <div class="sign">Customer Signature</div>
        </div>
    </div>

    <div class="footer">
        {{ $companyName }} | {{ $companyAddress }} | {{ $companyEmail }}
    </div>
</body>
</html>
