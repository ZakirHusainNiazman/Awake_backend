<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->id }} Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Order Report - #{{ $order->id }}</h2>

    <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <p><strong>Date:</strong> {{ optional($order->created_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->sku ?? 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total Quantity:</strong> {{ $totalQuantity }}</p>
    <p><strong>Order Total:</strong> ${{ number_format($subTotal, 2) }}</p>
</body>
</html>
