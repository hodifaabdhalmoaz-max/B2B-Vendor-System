<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الطلب رقم #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #28a745;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .order-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .order-number {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        .content {
            padding: 20px 0;
        }
        .message {
            font-size: 16px;
            line-height: 1.8;
            margin: 20px 0;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-details h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .info-item {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            border-right: 4px solid #28a745;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .info-value {
            color: #495057;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .total-section {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 16px;
        }
        .total-final {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            border-top: 2px solid #28a745;
            padding-top: 10px;
        }
        .shipping-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .shipping-info h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-ordered {
            background-color: #ffc107;
            color: #212529;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }
        .contact-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .contact-info h4 {
            color: #1565c0;
            margin-bottom: 10px;
        }
        .whatsapp-button {
            display: inline-block;
            background-color: #25d366;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 10px 0;
        }
        .whatsapp-button:hover {
            background-color: #128c7e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $appName }}</div>
            <p>متجر متخصص في منتجات الأطفال عالية الجودة</p>
        </div>

        <div class="content">
            <h1 class="order-title">✅ تم تأكيد طلبك بنجاح!</h1>
            
            <div style="text-align: center;">
                <span class="order-number">طلب رقم #{{ $order->id }}</span>
            </div>

            <p class="message">
                عزيزي/عزيزتي <strong>{{ $user->name }}</strong>،
            </p>

            <p class="message">
                شكراً لك على ثقتك بنا! تم استلام طلبك بنجاح وهو الآن قيد المعالجة. سنقوم بتحضير طلبك والتواصل معك قريباً لتأكيد التوصيل.
            </p>

            <div class="order-info">
                <div class="info-item">
                    <div class="info-label">📅 تاريخ الطلب:</div>
                    <div class="info-value">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">📊 حالة الطلب:</div>
                    <div class="info-value">
                        <span class="status-badge status-ordered">{{ $order->status }}</span>
                    </div>
                </div>
            </div>

            <div class="order-details">
                <h3>🛍️ تفاصيل الطلب:</h3>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                <br>
                                <small>رمز المنتج: {{ $item->product->SKU }}</small>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 2) }} ر.ي</td>
                            <td><strong>{{ number_format($item->quantity * $item->price, 2) }} ر.ي</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="total-section">
                <h3>💰 ملخص الفاتورة:</h3>
                <div class="total-row">
                    <span>المجموع الفرعي:</span>
                    <span>{{ number_format($order->subtotal, 2) }} ر.ي</span>
                </div>
                @if($order->discount > 0)
                <div class="total-row">
                    <span>الخصم:</span>
                    <span>-{{ number_format($order->discount, 2) }} ر.ي</span>
                </div>
                @endif

                <div class="total-row total-final">
                    <span>المجموع الإجمالي:</span>
                    <span>{{ number_format($order->total, 2) }} ر.ي</span>
                </div>
            </div>

            <div class="shipping-info">
                <h4>🚚 معلومات التوصيل:</h4>
                <p><strong>الاسم:</strong> {{ $order->name }}</p>
                <p><strong>الهاتف:</strong> {{ $order->phone }}</p>
                <p><strong>العنوان:</strong> {{ $order->address }}, {{ $order->city }}, {{ $order->state }}</p>
                @if($order->landmark)
                <p><strong>علامة مميزة:</strong> {{ $order->landmark }}</p>
                @endif
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <p><strong>💬 تواصل معنا عبر واتساب لأي استفسار:</strong></p>
                <a href="https://wa.me/967777548421?text=استفسار عن الطلب رقم {{ $order->id }}" class="whatsapp-button">
                    📱 تواصل عبر واتساب
                </a>
            </div>

            <div class="contact-info">
                <h4>📞 معلومات التواصل:</h4>
                <p><strong>الهاتف:</strong> +967 777 548 421 | +967 718 706 242</p>
                <p><strong>البريد الإلكتروني:</strong> hodifaabdhalmoaz@gmail.com</p>
                <p><strong>ساعات العمل:</strong> من السبت إلى الخميس، 9 صباحاً - 9 مساءً</p>
            </div>

            <p class="message">
                سنقوم بإرسال تحديثات حول حالة طلبك عبر البريد الإلكتروني والرسائل النصية. نتطلع لخدمتك مرة أخرى!
            </p>

            <p class="message">
                شكراً لاختيارك {{ $appName }} - حيث جودة منتجات الأطفال هي أولويتنا! 🎈
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $appName }}. جميع الحقوق محفوظة.</p>
            <p>تم التطوير بواسطة Hudhaifa Al-Hudhaifi</p>
            <p>
                <a href="{{ $appUrl }}" style="color: #28a745;">زيارة الموقع</a> |
                <a href="{{ $appUrl }}/orders" style="color: #28a745;">تتبع الطلبات</a> |
                <a href="{{ $appUrl }}/contact" style="color: #28a745;">اتصل بنا</a>
            </p>
        </div>
    </div>
</body>
</html>
