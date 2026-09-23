<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في {{ $appName }}</title>
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
            max-width: 600px;
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
            border-bottom: 2px solid #e74c3c;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .welcome-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .content {
            padding: 20px 0;
        }
        .user-name {
            font-size: 20px;
            color: #e74c3c;
            font-weight: bold;
        }
        .message {
            font-size: 16px;
            line-height: 1.8;
            margin: 20px 0;
        }
        .features {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .features h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .features ul {
            list-style: none;
            padding: 0;
        }
        .features li {
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .features li:last-child {
            border-bottom: none;
        }
        .features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-left: 10px;
        }
        .cta-button {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            background-color: #c0392b;
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
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .contact-info h4 {
            color: #155724;
            margin-bottom: 10px;
        }
        .social-links {
            text-align: center;
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #e74c3c;
            text-decoration: none;
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
            <h1 class="welcome-title">🎉 مرحباً بك في عائلتنا!</h1>
            
            <p class="message">
                عزيزي/عزيزتي <span class="user-name">{{ $user->name }}</span>،
            </p>

            <p class="message">
                نحن سعداء جداً بانضمامك إلى {{ $appName }}! شكراً لك على ثقتك بنا واختيارك لمتجرنا المتخصص في منتجات الأطفال عالية الجودة.
            </p>

            <div class="features">
                <h3>🌟 ما يميزنا:</h3>
                <ul>
                    <li>منتجات أطفال عالية الجودة ومضمونة</li>
                    <li>أسعار تنافسية ومناسبة للجميع</li>
                    <li>خدمة عملاء متميزة على مدار الساعة</li>
                    <li>توصيل سريع وآمن لجميع أنحاء اليمن</li>
                    <li>ضمان الجودة واستبدال المنتجات</li>
                    <li>عروض وخصومات حصرية للأعضاء</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="{{ $appUrl }}" class="cta-button">
                    🛍️ ابدأ التسوق الآن
                </a>
            </div>

            <div class="contact-info">
                <h4>📞 تواصل معنا:</h4>
                <p><strong>الهاتف:</strong> +967 777 548 421 | +967 718 706 242</p>
                <p><strong>البريد الإلكتروني:</strong> hodifaabdhalmoaz@gmail.com</p>
                <p><strong>واتساب:</strong> <a href="https://wa.me/967777548421">+967 777 548 421</a></p>
            </div>

            <div class="social-links">
                <p><strong>تابعنا على:</strong></p>
                <a href="#">📘 فيسبوك</a>
                <a href="#">📷 إنستغرام</a>
                <a href="#">🐦 تويتر</a>
                <a href="#">💼 لينكد إن</a>
            </div>

            <p class="message">
                إذا كان لديك أي استفسار أو تحتاج إلى مساعدة، لا تتردد في التواصل معنا. فريقنا جاهز لخدمتك في أي وقت.
            </p>

            <p class="message">
                مرة أخرى، أهلاً وسهلاً بك في {{ $appName }}! 🎈
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $appName }}. جميع الحقوق محفوظة.</p>
            <p>تم التطوير بواسطة Hudhaifa Al-Hudhaifi</p>
            <p>
                <a href="{{ $appUrl }}" style="color: #e74c3c;">زيارة الموقع</a> |
                <a href="{{ $appUrl }}/privacy" style="color: #e74c3c;">سياسة الخصوصية</a> |
                <a href="{{ $appUrl }}/contact" style="color: #e74c3c;">اتصل بنا</a>
            </p>
        </div>
    </div>
</body>
</html>
