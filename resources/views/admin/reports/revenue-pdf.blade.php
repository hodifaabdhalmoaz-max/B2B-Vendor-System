<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الإيرادات - {{ $period_label }}</title>
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
            border-bottom: 2px solid #2377FC;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2377FC;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2377FC;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        
        .change-indicator {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .positive {
            color: #28a745;
        }
        
        .negative {
            color: #dc3545;
        }
        
        .summary-section {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .summary-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .comparison-section {
            margin-top: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .comparison-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .comparison-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">متجر منتجات الأطفال</div>
        <div class="report-title">تقرير الإيرادات</div>
        <div class="report-subtitle">{{ $period_label }}</div>
    </div>

    <!-- Report Information -->
    <div class="info-section">
        <div class="info-row">
            <strong>الفترة الزمنية:</strong>
            <span>{{ $period_label }}</span>
        </div>
        <div class="info-row">
            <strong>من تاريخ:</strong>
            <span>{{ $date_range['start'] }}</span>
        </div>
        <div class="info-row">
            <strong>إلى تاريخ:</strong>
            <span>{{ $date_range['end'] }}</span>
        </div>
        <div class="info-row">
            <strong>تاريخ إنشاء التقرير:</strong>
            <span>{{ $generated_at }}</span>
        </div>
    </div>

    <!-- Main Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ format_price($analytics['current']['revenue']) }}</div>
            <div class="stat-label">إجمالي الإيرادات</div>
            @if($analytics['changes']['revenue'] != 0)
            <div class="change-indicator {{ $analytics['changes']['revenue'] >= 0 ? 'positive' : 'negative' }}">
                {{ $analytics['changes']['revenue'] >= 0 ? '+' : '' }}{{ $analytics['changes']['revenue'] }}% مقارنة بالفترة السابقة
            </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $analytics['current']['orders_count'] }}</div>
            <div class="stat-label">عدد الطلبات</div>
            @if($analytics['changes']['orders'] != 0)
            <div class="change-indicator {{ $analytics['changes']['orders'] >= 0 ? 'positive' : 'negative' }}">
                {{ $analytics['changes']['orders'] >= 0 ? '+' : '' }}{{ $analytics['changes']['orders'] }}% مقارنة بالفترة السابقة
            </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ format_price($analytics['current']['average_order_value']) }}</div>
            <div class="stat-label">متوسط قيمة الطلب</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $analytics['current']['delivered_count'] }}</div>
            <div class="stat-label">الطلبات المسلمة</div>
        </div>
    </div>

    <!-- Comparison with Previous Period -->
    <div class="comparison-section">
        <div class="comparison-title">مقارنة مع الفترة السابقة</div>
        
        <div class="comparison-item">
            <span>الإيرادات الحالية:</span>
            <span>{{ format_price($analytics['current']['revenue']) }}</span>
        </div>
        
        <div class="comparison-item">
            <span>الإيرادات السابقة:</span>
            <span>{{ format_price($analytics['previous']['revenue']) }}</span>
        </div>
        
        <div class="comparison-item">
            <span>الطلبات الحالية:</span>
            <span>{{ $analytics['current']['orders_count'] }}</span>
        </div>
        
        <div class="comparison-item">
            <span>الطلبات السابقة:</span>
            <span>{{ $analytics['previous']['orders_count'] }}</span>
        </div>
    </div>

    <!-- Overall Summary -->
    <div class="summary-section">
        <div class="summary-title">الملخص الإجمالي (جميع الأوقات)</div>
        
        <div class="summary-item">
            <span>إجمالي الإيرادات:</span>
            <span>{{ format_price($overall_summary['total_revenue']) }}</span>
        </div>
        
        <div class="summary-item">
            <span>إجمالي الطلبات:</span>
            <span>{{ $overall_summary['total_orders'] }}</span>
        </div>
        
        <div class="summary-item">
            <span>الطلبات المسلمة:</span>
            <span>{{ $overall_summary['total_delivered'] }}</span>
        </div>
        
        <div class="summary-item">
            <span>الطلبات المعلقة:</span>
            <span>{{ $overall_summary['total_pending'] }}</span>
        </div>
        
        <div class="summary-item">
            <span>الطلبات الملغاة:</span>
            <span>{{ $overall_summary['total_cancelled'] }}</span>
        </div>
    </div>

    <!-- Additional Insights -->
    @if($analytics['current']['orders_count'] > 0)
    <div class="summary-section">
        <div class="summary-title">رؤى إضافية</div>
        
        <div class="summary-item">
            <span>معدل التسليم:</span>
            <span>{{ round(($analytics['current']['delivered_count'] / $analytics['current']['orders_count']) * 100, 1) }}%</span>
        </div>
        
        @if($analytics['current']['total_orders'] > 0)
        <div class="summary-item">
            <span>معدل تحويل الإيرادات:</span>
            <span>{{ round(($analytics['current']['revenue'] / $analytics['current']['total_orders']) * 100, 1) }}%</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>تم إنشاء هذا التقرير تلقائياً بواسطة نظام إدارة متجر منتجات الأطفال</p>
        <p>تاريخ الإنشاء: {{ $generated_at }}</p>
        <p>© {{ date('Y') }} متجر منتجات الأطفال - جميع الحقوق محفوظة</p>
    </div>
</body>
</html>
