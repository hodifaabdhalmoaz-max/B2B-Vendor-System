<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنبيه صحة النظام</title>
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
            max-width: 700px;
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
        .priority-badge {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .alert-section {
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid {{ $statusColor }};
        }
        .critical-section {
            background: #f8d7da;
            border-right-color: #dc3545;
        }
        .warning-section {
            background: #fff3cd;
            border-right-color: #ffc107;
        }
        .issue-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .issue-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        .issue-item:last-child {
            border-bottom: none;
        }
        .issue-icon {
            margin-left: 10px;
            font-size: 16px;
        }
        .health-checks {
            margin: 30px 0;
        }
        .check-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .check-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .check-status {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .check-name {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
            color: #666;
        }
        .check-details {
            font-size: 11px;
            color: #888;
        }
        .actions {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .actions h4 {
            margin: 0 0 15px 0;
            color: #1976d2;
        }
        .action-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .action-item {
            padding: 5px 0;
            padding-right: 20px;
            position: relative;
        }
        .action-item:before {
            content: "→";
            position: absolute;
            right: 0;
            color: #1976d2;
            font-weight: bold;
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
            .check-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="priority-badge">{{ $priority }}</div>
            <div class="status-icon">{{ $statusIcon }}</div>
            <h1>تنبيه صحة النظام</h1>
            <p>{{ ucfirst($healthStatus['overall_status']) }}</p>
        </div>
        
        <div class="content">
            @if(!empty($criticalIssues))
            <div class="alert-section critical-section">
                <h3 style="margin: 0 0 15px 0; color: #721c24;">🚨 مشاكل حرجة</h3>
                <ul class="issue-list">
                    @foreach($criticalIssues as $issue)
                    <li class="issue-item">
                        <span class="issue-icon">❌</span>
                        <span>{{ $issue }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if(!empty($warnings))
            <div class="alert-section warning-section">
                <h3 style="margin: 0 0 15px 0; color: #856404;">⚠️ تحذيرات</h3>
                <ul class="issue-list">
                    @foreach($warnings as $warning)
                    <li class="issue-item">
                        <span class="issue-icon">⚠️</span>
                        <span>{{ $warning }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="health-checks">
                <h3>تفاصيل الفحوصات</h3>
                <div class="check-grid">
                    @foreach($healthStatus['checks'] as $checkName => $checkData)
                    <div class="check-item">
                        <div class="check-status">
                            @if($checkData['status'] === 'healthy')
                                ✅
                            @elseif($checkData['status'] === 'warning')
                                ⚠️
                            @else
                                ❌
                            @endif
                        </div>
                        <div class="check-name">{{ ucfirst(str_replace('_', ' ', $checkName)) }}</div>
                        <div class="check-details">
                            {{ ucfirst($checkData['status']) }}
                            @if(isset($checkData['error']))
                                <br><small>{{ $checkData['error'] }}</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            @if(!empty($criticalIssues) || !empty($warnings))
            <div class="actions">
                <h4>الإجراءات المطلوبة</h4>
                <ul class="action-list">
                    @if(!empty($criticalIssues))
                    <li class="action-item">فحص سجلات النظام للحصول على تفاصيل أكثر</li>
                    <li class="action-item">التحقق من حالة الخدمات الأساسية</li>
                    <li class="action-item">مراجعة استخدام الموارد (المعالج، الذاكرة، التخزين)</li>
                    @endif
                    @if(in_array('Database connection failed', $criticalIssues))
                    <li class="action-item">فحص اتصال قاعدة البيانات وإعدادات الشبكة</li>
                    @endif
                    @if(collect($criticalIssues)->contains(fn($issue) => str_contains($issue, 'backup')))
                    <li class="action-item">تشغيل النسخ الاحتياطي يدوياً والتحقق من الإعدادات</li>
                    @endif
                    <li class="action-item">تشغيل فحص صحة النظام مرة أخرى بعد الإصلاح</li>
                </ul>
            </div>
            @endif
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #495057;">معلومات إضافية</h4>
                <p style="margin: 0; font-size: 14px; color: #6c757d;">
                    <strong>وقت الفحص:</strong> {{ $timestamp }}<br>
                    <strong>الحالة العامة:</strong> {{ ucfirst($healthStatus['overall_status']) }}<br>
                    <strong>عدد المشاكل الحرجة:</strong> {{ count($criticalIssues) }}<br>
                    <strong>عدد التحذيرات:</strong> {{ count($warnings) }}
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>هذا تنبيه تلقائي من نظام مراقبة صحة النظام</p>
            <p class="timestamp">تم الإرسال في: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p style="margin-top: 15px;">
                <strong>{{ config('app.name') }}</strong><br>
                نظام إدارة المتجر الإلكتروني
            </p>
        </div>
    </div>
</body>
</html>
