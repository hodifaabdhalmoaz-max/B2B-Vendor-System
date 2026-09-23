<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة الطلب #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-section {
            width: 48%;
        }

        .info-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #007bff;
        }

        .info-content {
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .totals {
            float: left;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            border-bottom: 2px solid #007bff;
            color: #007bff;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">متجر الأطفال</div>
        <div class="invoice-title">فاتورة الطلب #{{ $order->id }}</div>
        <div>تاريخ الإصدار: {{ now()->format('Y-m-d') }}</div>
    </div>

    <div class="invoice-info">
        <div class="info-section">
            <div class="info-title">معلومات العميل:</div>
            <div class="info-content">
                <strong>الاسم:</strong> {{ $order->name }}<br>
                <strong>البريد الإلكتروني:</strong> {{ $order->email }}<br>
                <strong>الهاتف:</strong> {{ $order->phone }}<br>
                <strong>العنوان:</strong> {{ $order->address }}<br>
                <strong>المدينة:</strong> {{ $order->city }}<br>
                <strong>الرمز البريدي:</strong> {{ $order->zip }}
            </div>
        </div>
        <div class="info-section">
            <div class="info-title">معلومات الطلب:</div>
            <div class="info-content">
                <strong>رقم الطلب:</strong> {{ $order->id }}<br>
                <strong>تاريخ الطلب:</strong> {{ $order->created_at->format('Y-m-d H:i') }}<br>
                <strong>حالة الطلب:</strong>
                @if($order->status == 'ordered')
                مطلوب
                @elseif($order->status == 'processing')
                قيد المعالجة
                @elseif($order->status == 'shipped')
                تم الشحن
                @elseif($order->status == 'delivered')
                تم التسليم
                @elseif($order->status == 'canceled')
                ملغي
                @endif
                <br>
                <strong>طريقة الدفع:</strong> {{ $order->transaction->paymentMethod->name ?? 'غير محدد' }}
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>
                    {{ $item->product->name }}
                    <x-order-item-options :options="$item->options" compact />
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ format_price($item->price) }}</td>
                <td>{{ format_price($item->quantity * $item->price) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>{{ format_price($order->subtotal) }}</span>
        </div>

        <div class="total-row">
            <span>الشحن:</span>
            <span>{{ format_price($order->shipping_cost ?? 0) }}</span>
        </div>
        <div class="total-row final">
            <span>الإجمالي:</span>
            <span>{{ format_price($order->total) }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>شكراً لك على تسوقك معنا!</p>
        <p>للاستفسارات: hodifaabdhalmoaz@gmail.com | 777548421 967+</p>
    </div>
</body>

</html>
