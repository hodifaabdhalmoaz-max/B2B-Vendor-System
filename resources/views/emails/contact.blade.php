<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة جديدة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .field {
            margin-bottom: 20px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 10px;
        }
        .field-label {
            font-weight: 700;
            color: #4a5568;
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }
        .field-value {
            color: #2d3748;
            font-size: 16px;
            line-height: 1.6;
        }
        .message-box {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #718096;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تم استلام رسالة جديدة من الموقع</h1>
        </div>
        <div class="content">
            <div class="field">
                <span class="field-label">الاسم الكامل:</span>
                <span class="field-value">{{ $data['name'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">البريد الإلكتروني:</span>
                <span class="field-value">{{ $data['email'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">رقم الهاتف:</span>
                <span class="field-value">{{ $data['phone'] ?? 'غير متوفر' }}</span>
            </div>
            <div class="field">
                <span class="field-label">الموضوع:</span>
                <span class="field-value">{{ $data['subject'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">الرسالة:</span>
                <div class="message-box">{{ $data['message'] }}</div>
            </div>
        </div>
        <div class="footer">
            تم إرسال هذه الرسالة عبر نموذج الاتصال في متجر الأطفال
            <br>
            &copy; {{ date('Y') }} متجر دنيا الأطفال
        </div>
    </div>
</body>
</html>
