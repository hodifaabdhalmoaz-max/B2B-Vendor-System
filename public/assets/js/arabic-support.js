/**
 * Arabic Language Support JavaScript
 * دعم اللغة العربية - JavaScript
 * 
 * @author حذيفة الحذيفي
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Arabic Support Object
    const ArabicSupport = {
        
        // Check if current language is Arabic
        isArabic: function() {
            return document.documentElement.lang === 'ar' || 
                   document.documentElement.dir === 'rtl' ||
                   document.body.classList.contains('rtl');
        },

        // Convert Western numerals to Arabic-Indic numerals
        convertToArabicNumerals: function(text) {
            if (!this.isArabic()) return text;
            
            const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            
            let result = text.toString();
            for (let i = 0; i < western.length; i++) {
                result = result.replace(new RegExp(western[i], 'g'), arabic[i]);
            }
            return result;
        },

        // Convert Arabic-Indic numerals to Western numerals
        convertToWesternNumerals: function(text) {
            const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            
            let result = text.toString();
            for (let i = 0; i < arabic.length; i++) {
                result = result.replace(new RegExp(arabic[i], 'g'), western[i]);
            }
            return result;
        },

        // Format currency for Arabic
        formatCurrency: function(amount, currency = 'ر.س') {
            if (!this.isArabic()) {
                return currency + ' ' + amount.toLocaleString();
            }
            
            const formattedAmount = this.convertToArabicNumerals(amount.toLocaleString());
            return formattedAmount + ' ' + currency;
        },

        // Format date for Arabic
        formatDate: function(date, options = {}) {
            if (!this.isArabic()) {
                return new Intl.DateTimeFormat('en', options).format(date);
            }

            const arabicMonths = [
                'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
            ];

            const arabicDays = [
                'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'
            ];

            if (typeof date === 'string') {
                date = new Date(date);
            }

            const day = this.convertToArabicNumerals(date.getDate());
            const month = arabicMonths[date.getMonth()];
            const year = this.convertToArabicNumerals(date.getFullYear());
            const dayName = arabicDays[date.getDay()];

            if (options.weekday) {
                return `${dayName}، ${day} ${month} ${year}`;
            }

            return `${day} ${month} ${year}`;
        },

        // Initialize RTL support
        initRTL: function() {
            if (!this.isArabic()) return;

            // Add RTL class to body if not present
            if (!document.body.classList.contains('rtl')) {
                document.body.classList.add('rtl');
            }

            // Set direction attribute
            document.documentElement.dir = 'rtl';
            document.body.dir = 'rtl';

            // Fix Bootstrap components for RTL
            this.fixBootstrapRTL();

            // Fix form inputs
            this.fixFormInputs();

            // Fix navigation
            this.fixNavigation();

            // Fix modals
            this.fixModals();
        },

        // Fix Bootstrap components for RTL
        fixBootstrapRTL: function() {
            // Fix dropdown menus
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.right = '0';
                menu.style.left = 'auto';
            });

            // Fix tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
                const placement = element.getAttribute('data-bs-placement');
                if (placement === 'left') {
                    element.setAttribute('data-bs-placement', 'right');
                } else if (placement === 'right') {
                    element.setAttribute('data-bs-placement', 'left');
                }
            });

            // Fix popovers
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(element => {
                const placement = element.getAttribute('data-bs-placement');
                if (placement === 'left') {
                    element.setAttribute('data-bs-placement', 'right');
                } else if (placement === 'right') {
                    element.setAttribute('data-bs-placement', 'left');
                }
            });
        },

        // Fix form inputs for RTL
        fixFormInputs: function() {
            // Fix search inputs
            document.querySelectorAll('input[type="search"], .search-field__input').forEach(input => {
                input.style.textAlign = 'right';
                input.style.paddingRight = '3rem';
                input.style.paddingLeft = '1rem';
            });

            // Fix number inputs to remain LTR
            document.querySelectorAll('input[type="number"], .price-input, .quantity-input').forEach(input => {
                input.style.direction = 'ltr';
                input.style.textAlign = 'left';
            });

            // Fix email inputs to remain LTR
            document.querySelectorAll('input[type="email"]').forEach(input => {
                input.style.direction = 'ltr';
                input.style.textAlign = 'left';
            });

            // Fix URL inputs to remain LTR
            document.querySelectorAll('input[type="url"]').forEach(input => {
                input.style.direction = 'ltr';
                input.style.textAlign = 'left';
            });
        },

        // Fix navigation for RTL
        fixNavigation: function() {
            // Fix breadcrumbs
            document.querySelectorAll('.breadcrumb-item + .breadcrumb-item::before').forEach(item => {
                item.style.content = '"\\\\";';
                item.style.transform = 'scaleX(-1)';
            });

            // Fix pagination
            document.querySelectorAll('.pagination').forEach(pagination => {
                pagination.style.flexDirection = 'row-reverse';
            });
        },

        // Fix modals for RTL
        fixModals: function() {
            document.querySelectorAll('.modal').forEach(modal => {
                const header = modal.querySelector('.modal-header');
                const body = modal.querySelector('.modal-body');
                const footer = modal.querySelector('.modal-footer');

                if (header) header.style.textAlign = 'right';
                if (body) body.style.textAlign = 'right';
                if (footer) footer.style.justifyContent = 'flex-start';
            });
        },

        // Convert all numbers in the page to Arabic numerals
        convertPageNumbers: function() {
            if (!this.isArabic()) return;

            // Convert numbers in specific elements
            const selectors = [
                '.price', '.quantity', '.total', '.subtotal',
                '.product-count', '.cart-count', '.page-number',
                '.stat-number', '.counter', '.badge'
            ];

            selectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(element => {
                    if (!element.classList.contains('keep-western')) {
                        element.textContent = this.convertToArabicNumerals(element.textContent);
                    }
                });
            });
        },

        // Initialize date formatting
        initDateFormatting: function() {
            if (!this.isArabic()) return;

            document.querySelectorAll('.date, [data-date]').forEach(element => {
                const dateValue = element.getAttribute('data-date') || element.textContent;
                const date = new Date(dateValue);
                
                if (!isNaN(date.getTime())) {
                    element.textContent = this.formatDate(date);
                }
            });
        },

        // Initialize currency formatting
        initCurrencyFormatting: function() {
            if (!this.isArabic()) return;

            document.querySelectorAll('.currency, .price').forEach(element => {
                const text = element.textContent;
                const amount = parseFloat(text.replace(/[^\d.]/g, ''));
                
                if (!isNaN(amount)) {
                    element.textContent = this.formatCurrency(amount);
                }
            });
        },

        // Handle form submissions with Arabic numerals
        handleFormSubmissions: function() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    // Convert Arabic numerals back to Western for processing
                    form.querySelectorAll('input[type="number"], .numeric-input').forEach(input => {
                        input.value = this.convertToWesternNumerals(input.value);
                    });
                });
            });
        },

        // Initialize all Arabic support features
        init: function() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
                return;
            }

            this.initRTL();
            this.convertPageNumbers();
            this.initDateFormatting();
            this.initCurrencyFormatting();
            this.handleFormSubmissions();

            // Re-run conversions when content is dynamically loaded
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        this.convertPageNumbers();
                        this.initDateFormatting();
                        this.initCurrencyFormatting();
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            console.log('Arabic Support initialized - تم تفعيل الدعم العربي');
        }
    };

    // Auto-initialize when script loads
    ArabicSupport.init();

    // Make ArabicSupport globally available
    window.ArabicSupport = ArabicSupport;

})();
