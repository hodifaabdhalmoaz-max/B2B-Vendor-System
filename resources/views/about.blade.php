@extends('layouts.app')

@section('title', 'من نحن - متجر الأطفال')
@section('description', 'تعرف على قصتنا ورؤيتنا في متجر الأطفال - نحن نؤمن بأن كل طفل يستحق الأفضل')
@section('meta_description', 'تعرف على قصتنا ورؤيتنا في متجر الأطفال - نحن نؤمن بأن كل طفل يستحق الأفضل')

@push('styles')
<style>
    .about-hero {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        color: white;
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }

    .about-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="about-pattern" width="40" height="40" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="10" cy="10" r="1" fill="white" opacity="0.05"/><circle cx="30" cy="30" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23about-pattern)"/></svg>');
        pointer-events: none;
    }

    .about-card {
        background: white;
        border-radius: 25px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        padding: 50px;
        margin-bottom: 40px;
        border: 1px solid #f0f4f8;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }

    .about-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(to bottom, #f0c14b, #d4a853);
        border-radius: 0 25px 25px 0;
    }

    .about-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
    }

    .about-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #f0c14b, #d4a853);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 10px 30px rgba(240, 193, 75, 0.3);
    }

    .about-section-title {
        color: #1a202c;
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 1.8rem;
        position: relative;
    }

    .about-section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        right: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #f0c14b, #d4a853);
        border-radius: 2px;
    }

    .about-content {
        color: #4a5568;
        line-height: 1.9;
        font-size: 16px;
    }

    .stats-card {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        color: white;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }

    .stats-number {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 10px;
        display: block;
    }

    .stats-label {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .team-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f4f8;
        margin-bottom: 30px;
    }

    .team-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
    }

    .team-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #f0c14b, #d4a853);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2rem;
        font-weight: 700;
    }

    .breadcrumb-about {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border-radius: 50px;
        padding: 15px 30px;
        margin-bottom: 50px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .breadcrumb-about a {
        color: #f0c14b;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .breadcrumb-about a:hover {
        color: #d4a853;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 50px 0;
    }

    .value-item {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f0f4f8;
    }

    .value-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .value-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f0c14b, #d4a853);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
    }

    .cta-section {
        background: linear-gradient(135deg, #f0c14b 0%, #d4a853 100%);
        border-radius: 25px;
        padding: 60px 40px;
        text-align: center;
        color: white;
        margin: 60px 0;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: pulse 8s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.5;
        }

        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    .btn-about {
        background: white;
        color: #f0c14b;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin: 10px;
    }

    .btn-about:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        color: #d4a853;
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 80px 0;
        }

        .about-card {
            padding: 30px;
            margin-bottom: 25px;
        }

        .about-icon {
            width: 70px;
            height: 70px;
        }

        .stats-number {
            font-size: 2.5rem;
        }

        .values-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="about-hero">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="breadcrumb-about">
                    <a href="{{ route('home.index') }}">
                        <i data-lucide="home" style="width: 18px; height: 18px;"></i>
                        الرئيسية
                    </a>
                    <i data-lucide="chevron-left" style="width: 16px; height: 16px; color: #a0aec0;"></i>
                    <span style="color: #1a202c; font-weight: 700;">من نحن</span>
                </div>

                <h1 class="display-3 fw-bold mb-4">من نحن</h1>
                <p class="lead mb-0">نحن أكثر من مجرد متجر - نحن شركاء في رحلة نمو أطفالكم</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Our Story Section -->
                <div class="about-card">
                    <div class="about-icon">
                        <i data-lucide="heart" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h2 class="about-section-title">قصتنا</h2>
                    <div class="about-content">
                        <p class="lead mb-4">
                            بدأت رحلتنا من إيمان عميق بأن كل طفل يستحق الأفضل. نحن نؤمن بأن منتجات الأطفال يجب أن تكون آمنة وعالية الجودة ومصممة خصيصاً لتلبية احتياجات الأطفال في مراحل نموهم المختلفة.
                        </p>
                        <p class="mb-4">
                            منذ تأسيسنا، نسعى جاهدين لتوفير تشكيلة واسعة من المنتجات المختارة بعناية، من الألعاب التعليمية إلى منتجات العناية والملابس، كل ذلك بأسعار مناسبة وجودة لا تُضاهى.
                        </p>
                        <p>
                            نحن نفهم أن الأطفال هم أغلى ما نملك، ولذلك نضع السلامة والجودة في المقدمة دائماً. كل منتج في متجرنا يمر بعملية فحص دقيقة لضمان مطابقته لأعلى معايير الجودة والأمان.
                        </p>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="row mb-5">
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <span class="stats-number" data-target="{{ $productCount }}">+{{ $productCount }}</span>
                            <span class="stats-label">منتج متنوع</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <span class="stats-number" data-target="{{ $customerCount }}">+{{ $customerCount }}</span>
                            <span class="stats-label">عميل سعيد</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <span class="stats-number">3</span>
                            <span class="stats-label">سنوات خبرة</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <span class="stats-number">24/7</span>
                            <span class="stats-label">دعم العملاء</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission & Vision Section -->
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="about-card">
                    <div class="about-icon">
                        <i data-lucide="target" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 class="about-section-title">رسالتنا</h3>
                    <div class="about-content">
                        <p>
                            توفير منتجات آمنة وعالية الجودة للأطفال بأسعار مناسبة، مع تقديم خدمة عملاء متميزة تضمن رضا العائلات وسعادة الأطفال في كل مرحلة من مراحل نموهم.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-card">
                    <div class="about-icon">
                        <i data-lucide="eye" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 class="about-section-title">رؤيتنا</h3>
                    <div class="about-content">
                        <p>
                            أن نكون الخيار الأول للعائلات في اليمن عند البحث عن منتجات الأطفال، ونساهم في بناء جيل سعيد وصحي ومبدع من خلال منتجات تعليمية وترفيهية متميزة.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Values Section -->
        <div class="about-card">
            <div class="about-icon">
                <i data-lucide="star" style="width: 32px; height: 32px;"></i>
            </div>
            <h2 class="about-section-title">قيمنا</h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">
                        <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h4 class="mb-3">الأمان أولاً</h4>
                    <p class="text-muted">نضع سلامة الأطفال في المقدمة ونختار منتجات تلتزم بأعلى معايير الأمان العالمية</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i data-lucide="award" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h4 class="mb-3">الجودة العالية</h4>
                    <p class="text-muted">نختار منتجاتنا بعناية فائقة لضمان الحصول على أفضل جودة بأسعار مناسبة</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i data-lucide="heart-handshake" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h4 class="mb-3">خدمة العملاء</h4>
                    <p class="text-muted">نقدم خدمة عملاء متميزة ونهتم بكل تفصيلة لضمان رضا عملائنا الكرام</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i data-lucide="lightbulb" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h4 class="mb-3">الابتكار</h4>
                    <p class="text-muted">نسعى دائماً لتقديم منتجات مبتكرة تساعد في تنمية قدرات الأطفال الإبداعية</p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="about-card">
            <div class="about-icon">
                <i data-lucide="users" style="width: 32px; height: 32px;"></i>
            </div>
            <h2 class="about-section-title">فريقنا</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i data-lucide="user" style="width: 40px; height: 40px;"></i>
                        </div>
                        <h5 class="mb-2">حذيفة عبد المعز</h5>
                        <p class="text-muted mb-3">المؤسس والمدير التنفيذي</p>
                        <p class="small">خبرة واسعة في مجال منتجات الأطفال وإدارة الأعمال</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i data-lucide="headphones" style="width: 40px; height: 40px;"></i>
                        </div>
                        <h5 class="mb-2">فريق خدمة العملاء</h5>
                        <p class="text-muted mb-3">دعم العملاء</p>
                        <p class="small">متاح 24/7 لخدمتكم والإجابة على جميع استفساراتكم</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i data-lucide="truck" style="width: 40px; height: 40px;"></i>
                        </div>
                        <h5 class="mb-2">فريق التوصيل</h5>
                        <p class="text-muted mb-3">الشحن والتوصيل</p>
                        <p class="small">ضمان وصول طلباتكم بأمان وفي الوقت المحدد</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h3 class="mb-4">
                <i data-lucide="heart" style="width: 28px; height: 28px; margin-left: 10px;"></i>
                كونوا جزئا من عائلة متجر دنيا الأطفال
            </h3>
            <p class="mb-4 lead">اكتشف مجموعتنا الواسعة من المنتجات المختارة بعناية لأطفالكم</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ route('shop.index') }}" class="btn-about">
                    <i data-lucide="shopping-bag" style="width: 18px; height: 18px;"></i>
                    تسوق الآن
                </a>
                <a href="{{ route('contact') }}" class="btn-about">
                    <i data-lucide="message-circle" style="width: 18px; height: 18px;"></i>
                    تواصل معنا
                </a>
            </div>
        </div>

    </div>
    </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Add smooth scrolling for anchor links 
    // Lucide initialization removed as it's already in the layout

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.about-card, .stats-card, .team-card, .value-item');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Counter animation for stats
    const counters = document.querySelectorAll('.stats-number');
    counters.forEach(counter => {
    const target = parseInt(counter.textContent.replace(/\D/g, ''));
    const increment = target / 100;
    let current = 0;

    const updateCounter = () => {
        if (current < target) {
            current += increment;
            counter.textContent = Math.ceil(current) + (counter.textContent.includes('+') ? '+' : '');
            requestAnimationFrame(updateCounter);
        } else {
            counter.textContent = counter.textContent;
        }
    };

    observer.observe(counter.closest('.stats-card'));
    counter.closest('.stats-card').addEventListener('animationstart', updateCounter);
    });
    });
</script>
@endpush
