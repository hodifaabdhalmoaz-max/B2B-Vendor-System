<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق بخطوتين</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            background-color: #f5f5f5;
            border-radius: 5px;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>رمز التحقق بخطوتين</h1>
        </div>
        <div class="content">
            <p>مرحبًا،</p>
            <p>لقد طلبت رمز التحقق بخطوتين للدخول إلى حسابك في متجر الأطفال. الرجاء استخدام الرمز التالي لإكمال عملية تسجيل الدخول:</p>
            <div class="code">{{ $code }}</div>
            <p>هذا الرمز صالح لمدة 10 دقائق فقط.</p>
            <p>إذا لم تطلب هذا الرمز، الرجاء تجاهل هذا البريد الإلكتروني أو الاتصال بفريق الدعم الفني.</p>
            <p>شكرًا لك،<br>فريق متجر الأطفال</p>
        </div>
        <div class="footer">
            <p>هذا البريد الإلكتروني تم إرساله تلقائيًا، الرجاء عدم الرد عليه.</p>
            <p>&copy; {{ date('Y') }} متجر الأطفال. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
