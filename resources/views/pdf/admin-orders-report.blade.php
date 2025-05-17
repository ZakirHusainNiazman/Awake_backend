<!DOCTYPE html>
<html>
<head>
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; font-size: 12px; }
        th { background-color: #f0f0f0; }
        h2 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h2>Orders Report</h2>
    <p><strong>Total Revenue:</strong> ${{ number_format($totalRevenue, 2) }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Status</th>
                <th>Items</th>
                <th>Qty</th>
                <th>Shipping</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>
                        @foreach ($order->items as $item)
                            <div>{{ $item->product_name }} (x{{ $item->quantity }})</div>
                        @endforeach
                    </td>
                    <td>{{ $order->items->sum('quantity') }}</td>
                    <td>${{ number_format($order->shipping_cost, 2) }}</td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
