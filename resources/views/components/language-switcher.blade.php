<div class="language-switcher">
    <div class="dropdown">
        <button class="btn dropdown-toggle current-language" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            @if(app()->getLocale() == 'en')
                <img src="{{ asset('assets/images/flags/en.png') }}" alt="English" width="20" height="15"> <span>English</span>
            @elseif(app()->getLocale() == 'ar')
                <img src="{{ asset('assets/images/flags/ar.png') }}" alt="Arabic" width="20" height="15"> <span>العربية</span>
            @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
            <li>
                <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="{{ route('language.switch', 'en') }}" data-tooltip="Switch to English">
                    <img src="{{ asset('assets/images/flags/en.png') }}" alt="English" width="20" height="15"> English
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ app()->getLocale() == 'ar' ? 'active' : '' }}" href="{{ route('language.switch', 'ar') }}" data-tooltip="التبديل إلى العربية">
                    <img src="{{ asset('assets/images/flags/ar.png') }}" alt="Arabic" width="20" height="15"> العربية
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation class to language switcher
    const languageSwitcher = document.querySelector('.language-switcher');
    if (languageSwitcher) {
        languageSwitcher.classList.add('lang-transition');
    }

    // Add tooltip functionality
    const languageLinks = document.querySelectorAll('.language-switcher .dropdown-item');
    languageLinks.forEach(link => {
        link.classList.add('language-tooltip');
    });

    // Add flag pulse animation on hover
    const flags = document.querySelectorAll('.language-switcher img');
    flags.forEach(flag => {
        flag.addEventListener('mouseenter', function() {
            this.classList.add('flag-pulse');
        });
        flag.addEventListener('mouseleave', function() {
            this.classList.remove('flag-pulse');
        });
    });
});
</script>
