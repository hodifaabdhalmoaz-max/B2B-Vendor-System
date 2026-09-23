/**
 * تحسينات الشريط الجانبي - إصلاح مشكلة السهم والقوائم الفرعية
 * تم إنشاؤه بواسطة: حذيفة الحذيفي
 */

(function() {
    'use strict';
    
    console.log('🔧 تحميل تحسينات الشريط الجانبي...');
    
    // انتظار تحميل DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
    
    function initSidebar() {
        console.log('✅ بدء تهيئة الشريط الجانبي...');
        
        // إعداد القوائم الفرعية
        setupSubMenus();
        
        // إعداد أحداث النقر
        setupClickEvents();
        
        // تمييز الصفحة النشطة
        highlightActivePage();
        
        // إعداد تأثيرات الحركة
        setupAnimations();
        
        console.log('🎉 تم تهيئة الشريط الجانبي بنجاح!');
    }
    
    function setupSubMenus() {
        const subMenus = document.querySelectorAll('.sub-menu');
        const menuItems = document.querySelectorAll('.menu-item');
        
        console.log(`📋 العثور على ${subMenus.length} قائمة فرعية و ${menuItems.length} عنصر قائمة`);
        
        // إظهار جميع عناصر القائمة
        menuItems.forEach(function(item) {
            item.style.display = 'list-item';
            item.style.visibility = 'visible';
            item.style.opacity = '1';
        });
        
        // إخفاء جميع القوائم الفرعية افتراضياً
        subMenus.forEach(function(subMenu) {
            subMenu.style.display = 'none';
            subMenu.style.visibility = 'hidden';
            subMenu.style.opacity = '0';
            subMenu.style.maxHeight = '0';
            subMenu.style.overflow = 'hidden';
        });
        
        // إزالة جميع الحالات النشطة
        const hasChildrenItems = document.querySelectorAll('.menu-item.has-children');
        hasChildrenItems.forEach(function(item) {
            item.classList.remove('active');
        });
    }
    
    function setupClickEvents() {
        const menuButtons = document.querySelectorAll('.menu-item.has-children .menu-item-button');
        
        console.log(`🖱️ إعداد أحداث النقر لـ ${menuButtons.length} زر قائمة`);
        
        menuButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const parentItem = this.closest('.menu-item.has-children');
                const subMenu = parentItem.querySelector('.sub-menu');
                const arrowIcon = this.querySelector('.arrow-icon');
                
                if (!subMenu) return;
                
                console.log('🔄 تبديل حالة القائمة:', parentItem.querySelector('.text').textContent);
                
                // إغلاق جميع القوائم الأخرى
                closeAllMenus(parentItem);
                
                // تبديل حالة القائمة الحالية
                if (parentItem.classList.contains('active')) {
                    closeMenu(parentItem, subMenu, arrowIcon);
                } else {
                    openMenu(parentItem, subMenu, arrowIcon);
                }
            });
        });
        
        // منع إغلاق القائمة عند النقر على العناصر الفرعية
        const subMenuItems = document.querySelectorAll('.sub-menu-item');
        subMenuItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    }
    
    function closeAllMenus(exceptItem) {
        const allMenuItems = document.querySelectorAll('.menu-item.has-children');
        
        allMenuItems.forEach(function(item) {
            if (item !== exceptItem && item.classList.contains('active')) {
                const subMenu = item.querySelector('.sub-menu');
                const arrowIcon = item.querySelector('.arrow-icon');
                closeMenu(item, subMenu, arrowIcon);
            }
        });
    }
    
    function openMenu(parentItem, subMenu, arrowIcon) {
        console.log('📂 فتح القائمة');
        
        // إضافة الحالة النشطة
        parentItem.classList.add('active');
        
        // تحريك السهم
        if (arrowIcon) {
            arrowIcon.style.transform = 'rotate(180deg)';
        }
        
        // إظهار القائمة الفرعية مع تأثير
        subMenu.style.display = 'block';
        subMenu.style.visibility = 'visible';
        
        // تأثير الفتح التدريجي
        setTimeout(function() {
            subMenu.style.opacity = '1';
            subMenu.style.maxHeight = '500px';
        }, 10);
    }
    
    function closeMenu(parentItem, subMenu, arrowIcon) {
        console.log('📁 إغلاق القائمة');
        
        // إزالة الحالة النشطة
        parentItem.classList.remove('active');
        
        // إعادة تدوير السهم
        if (arrowIcon) {
            arrowIcon.style.transform = 'rotate(0deg)';
        }
        
        // إخفاء القائمة الفرعية مع تأثير
        subMenu.style.opacity = '0';
        subMenu.style.maxHeight = '0';
        
        setTimeout(function() {
            subMenu.style.display = 'none';
            subMenu.style.visibility = 'hidden';
        }, 300);
    }
    
    function highlightActivePage() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-item a, .sub-menu-item a');
        
        console.log('🎯 تمييز الصفحة النشطة:', currentPath);
        
        menuLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                link.classList.add('active');
                
                // إذا كان الرابط في قائمة فرعية، افتح القائمة الرئيسية
                const parentSubMenu = link.closest('.sub-menu');
                if (parentSubMenu) {
                    const parentMenuItem = parentSubMenu.closest('.menu-item.has-children');
                    if (parentMenuItem) {
                        const arrowIcon = parentMenuItem.querySelector('.arrow-icon');
                        openMenu(parentMenuItem, parentSubMenu, arrowIcon);
                    }
                }
            }
        });
    }
    
    function setupAnimations() {
        // إضافة تأثيرات CSS للحركة
        const style = document.createElement('style');
        style.textContent = `
            .menu-item-button {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .arrow-icon {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .sub-menu {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            
            .menu-item.active .menu-item-button {
                background-color: #e3f2fd !important;
                color: #1976d2 !important;
                font-weight: 600 !important;
            }
            
            .menu-item.active .arrow-icon {
                color: #1976d2 !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    // دالة للتحقق من حالة القوائم
    function debugMenuState() {
        const hasChildrenItems = document.querySelectorAll('.menu-item.has-children');
        console.log('🔍 حالة القوائم:');
        
        hasChildrenItems.forEach(function(item, index) {
            const text = item.querySelector('.text').textContent;
            const isActive = item.classList.contains('active');
            const subMenu = item.querySelector('.sub-menu');
            const isVisible = subMenu && subMenu.style.display !== 'none';
            
            console.log(`${index + 1}. ${text}: نشط=${isActive}, مرئي=${isVisible}`);
        });
    }
    
    // إضافة دالة للتحكم من وحدة التحكم
    window.debugSidebar = debugMenuState;
    
    // إعادة تهيئة القائمة كل ثانية للتأكد
    setInterval(function() {
        const menuList = document.querySelector('.menu-list');
        if (menuList && menuList.style.display === 'none') {
            console.log('🔄 إعادة تهيئة القائمة...');
            setupSubMenus();
        }
    }, 2000);
    
})();

console.log('📦 تم تحميل ملف تحسينات الشريط الجانبي');
