<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار النسخ الاحتياطي</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, {{ $statusColor }}, {{ $statusColor }}dd);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }
        .status-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-right: 4px solid {{ $statusColor }};
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            color: #333;
        }
        .results-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .result-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .timestamp {
            color: #999;
            font-size: 12px;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="status-icon">{{ $statusIcon }}</div>
            <h1>تقرير النسخ الاحتياطي</h1>
            <p>{{ $backupType }}</p>
        </div>
        
        <div class="content">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">الحالة</div>
                    <div class="info-value">{{ ucfirst($status) }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">المدة الزمنية</div>
                    <div class="info-value">{{ $duration }} ثانية</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">حجم الملف</div>
                    <div class="info-value">{{ $fileSize }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">التوقيت</div>
                    <div class="info-value">{{ $timestamp }}</div>
                </div>
            </div>
            
            @if(!empty($results))
            <div class="results-section">
                <h3>تفاصيل النتائج</h3>
                
                @if(isset($results['database']))
                <div class="result-item">
                    <span>نسخ احتياطي لقاعدة البيانات</span>
                    <span class="result-status {{ $results['database'] ? 'status-success' : 'status-failed' }}">
                        {{ $results['database'] ? 'نجح' : 'فشل' }}
                    </span>
                </div>
                @endif
                
                @if(isset($results['files']))
                <div class="result-item">
                    <span>نسخ احتياطي للملفات</span>
                    <span class="result-status {{ $results['files'] ? 'status-success' : 'status-failed' }}">
                        {{ $results['files'] ? 'نجح' : 'فشل' }}
                    </span>
                </div>
                @endif
            </div>
            @endif
            
            <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #1976d2;">معلومات مهمة</h4>
                <ul style="margin: 0; padding-right: 20px;">
                    <li>يتم حفظ النسخ الاحتياطية في مجلد آمن ومشفر</li>
                    <li>يتم حذف النسخ القديمة تلقائياً حسب السياسة المحددة</li>
                    <li>في حالة وجود مشاكل، يرجى التواصل مع فريق الدعم الفني</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>هذا إشعار تلقائي من نظام النسخ الاحتياطي</p>
            <p class="timestamp">تم الإرسال في: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p style="margin-top: 15px;">
                <strong>{{ config('app.name') }}</strong><br>
                نظام إدارة المتجر الإلكتروني
            </p>
        </div>
    </div>
</body>
</html>
