<?php
/**
 * Script to generate language files for all supported languages
 * This is a one-time generation script
 */

// Language mappings: ISO code => [language name, sample translations]
$languages = [
    'ru' => ['Russian', [
        'nav.home' => 'Главная',
        'nav.stories' => 'Истории',
        'nav.about' => 'О нас',
        'nav.contact' => 'Контакты',
        'nav.login' => 'Войти',
        'nav.register' => 'Регистрация',
        'nav.logout' => 'Выйти',
        'nav.profile' => 'Профиль',
        'btn.save' => 'Сохранить',
        'btn.cancel' => 'Отмена',
        'btn.delete' => 'Удалить',
        'btn.edit' => 'Редактировать',
        'auth.login.title' => 'Вход в RenalTales',
        'stories.title' => 'Истории RenalTales',
        'home.hero.title' => 'Добро пожаловать в RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Все права защищены.'
    ]],
    
    'uk' => ['Ukrainian', [
        'nav.home' => 'Головна',
        'nav.stories' => 'Історії',
        'nav.about' => 'Про нас',
        'nav.contact' => 'Контакти',
        'nav.login' => 'Увійти',
        'nav.register' => 'Реєстрація',
        'nav.logout' => 'Вийти',
        'nav.profile' => 'Профіль',
        'btn.save' => 'Зберегти',
        'btn.cancel' => 'Скасувати',
        'btn.delete' => 'Видалити',
        'btn.edit' => 'Редагувати',
        'auth.login.title' => 'Вхід до RenalTales',
        'stories.title' => 'Історії RenalTales',
        'home.hero.title' => 'Ласкаво просимо до RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Усі права захищені.'
    ]],
    
    'pl' => ['Polish', [
        'nav.home' => 'Strona główna',
        'nav.stories' => 'Historie',
        'nav.about' => 'O nas',
        'nav.contact' => 'Kontakt',
        'nav.login' => 'Zaloguj się',
        'nav.register' => 'Zarejestruj się',
        'nav.logout' => 'Wyloguj się',
        'nav.profile' => 'Profil',
        'btn.save' => 'Zapisz',
        'btn.cancel' => 'Anuluj',
        'btn.delete' => 'Usuń',
        'btn.edit' => 'Edytuj',
        'auth.login.title' => 'Zaloguj się do RenalTales',
        'stories.title' => 'Historie RenalTales',
        'home.hero.title' => 'Witamy w RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Wszystkie prawa zastrzeżone.'
    ]],
    
    'hu' => ['Hungarian', [
        'nav.home' => 'Főoldal',
        'nav.stories' => 'Történetek',
        'nav.about' => 'Rólunk',
        'nav.contact' => 'Kapcsolat',
        'nav.login' => 'Bejelentkezés',
        'nav.register' => 'Regisztráció',
        'nav.logout' => 'Kijelentkezés',
        'nav.profile' => 'Profil',
        'btn.save' => 'Mentés',
        'btn.cancel' => 'Mégse',
        'btn.delete' => 'Törlés',
        'btn.edit' => 'Szerkesztés',
        'auth.login.title' => 'Bejelentkezés a RenalTales-be',
        'stories.title' => 'RenalTales Történetek',
        'home.hero.title' => 'Üdvözöljük a RenalTales-ben',
        'footer.copyright' => '© 2025 RenalTales. Minden jog fenntartva.'
    ]],
    
    'ro' => ['Romanian', [
        'nav.home' => 'Acasă',
        'nav.stories' => 'Povești',
        'nav.about' => 'Despre noi',
        'nav.contact' => 'Contact',
        'nav.login' => 'Autentificare',
        'nav.register' => 'Înregistrare',
        'nav.logout' => 'Deconectare',
        'nav.profile' => 'Profil',
        'btn.save' => 'Salvează',
        'btn.cancel' => 'Anulează',
        'btn.delete' => 'Șterge',
        'btn.edit' => 'Editează',
        'auth.login.title' => 'Autentificare în RenalTales',
        'stories.title' => 'Povești RenalTales',
        'home.hero.title' => 'Bun venit la RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Toate drepturile rezervate.'
    ]],
    
    'bg' => ['Bulgarian', [
        'nav.home' => 'Начало',
        'nav.stories' => 'Истории',
        'nav.about' => 'За нас',
        'nav.contact' => 'Контакт',
        'nav.login' => 'Вход',
        'nav.register' => 'Регистрация',
        'nav.logout' => 'Изход',
        'nav.profile' => 'Профил',
        'btn.save' => 'Запази',
        'btn.cancel' => 'Отказ',
        'btn.delete' => 'Изтрий',
        'btn.edit' => 'Редактирай',
        'auth.login.title' => 'Вход в RenalTales',
        'stories.title' => 'Истории от RenalTales',
        'home.hero.title' => 'Добре дошли в RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Всички права запазени.'
    ]],
    
    'sl' => ['Slovenian', [
        'nav.home' => 'Domov',
        'nav.stories' => 'Zgodbe',
        'nav.about' => 'O nas',
        'nav.contact' => 'Stik',
        'nav.login' => 'Prijava',
        'nav.register' => 'Registracija',
        'nav.logout' => 'Odjava',
        'nav.profile' => 'Profil',
        'btn.save' => 'Shrani',
        'btn.cancel' => 'Prekliči',
        'btn.delete' => 'Izbriši',
        'btn.edit' => 'Uredi',
        'auth.login.title' => 'Prijava v RenalTales',
        'stories.title' => 'Zgodbe RenalTales',
        'home.hero.title' => 'Dobrodošli v RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Vse pravice pridržane.'
    ]],
    
    'hr' => ['Croatian', [
        'nav.home' => 'Početna',
        'nav.stories' => 'Priče',
        'nav.about' => 'O nama',
        'nav.contact' => 'Kontakt',
        'nav.login' => 'Prijava',
        'nav.register' => 'Registracija',
        'nav.logout' => 'Odjava',
        'nav.profile' => 'Profil',
        'btn.save' => 'Spremi',
        'btn.cancel' => 'Odustani',
        'btn.delete' => 'Obriši',
        'btn.edit' => 'Uredi',
        'auth.login.title' => 'Prijava u RenalTales',
        'stories.title' => 'RenalTales Priče',
        'home.hero.title' => 'Dobrodošli u RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Sva prava pridržana.'
    ]],
    
    'sr' => ['Serbian', [
        'nav.home' => 'Почетна',
        'nav.stories' => 'Приче',
        'nav.about' => 'О нама',
        'nav.contact' => 'Контакт',
        'nav.login' => 'Пријава',
        'nav.register' => 'Регистрација',
        'nav.logout' => 'Одјава',
        'nav.profile' => 'Профил',
        'btn.save' => 'Сачувај',
        'btn.cancel' => 'Откажи',
        'btn.delete' => 'Обриши',
        'btn.edit' => 'Уреди',
        'auth.login.title' => 'Пријава у RenalTales',
        'stories.title' => 'RenalTales Приче',
        'home.hero.title' => 'Добродошли у RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Сва права задржана.'
    ]],
    
    'ja' => ['Japanese', [
        'nav.home' => 'ホーム',
        'nav.stories' => 'ストーリー',
        'nav.about' => '私たちについて',
        'nav.contact' => 'お問い合わせ',
        'nav.login' => 'ログイン',
        'nav.register' => '登録',
        'nav.logout' => 'ログアウト',
        'nav.profile' => 'プロフィール',
        'btn.save' => '保存',
        'btn.cancel' => 'キャンセル',
        'btn.delete' => '削除',
        'btn.edit' => '編集',
        'auth.login.title' => 'RenalTalesにログイン',
        'stories.title' => 'RenalTalesストーリー',
        'home.hero.title' => 'RenalTalesへようこそ',
        'footer.copyright' => '© 2025 RenalTales. 全著作権所有。'
    ]],
    
    'zh' => ['Chinese Simplified', [
        'nav.home' => '首页',
        'nav.stories' => '故事',
        'nav.about' => '关于我们',
        'nav.contact' => '联系我们',
        'nav.login' => '登录',
        'nav.register' => '注册',
        'nav.logout' => '退出',
        'nav.profile' => '个人资料',
        'btn.save' => '保存',
        'btn.cancel' => '取消',
        'btn.delete' => '删除',
        'btn.edit' => '编辑',
        'auth.login.title' => '登录RenalTales',
        'stories.title' => 'RenalTales故事',
        'home.hero.title' => '欢迎来到RenalTales',
        'footer.copyright' => '© 2025 RenalTales. 版权所有。'
    ]],
    
    'ko' => ['Korean', [
        'nav.home' => '홈',
        'nav.stories' => '이야기',
        'nav.about' => '소개',
        'nav.contact' => '연락처',
        'nav.login' => '로그인',
        'nav.register' => '등록',
        'nav.logout' => '로그아웃',
        'nav.profile' => '프로필',
        'btn.save' => '저장',
        'btn.cancel' => '취소',
        'btn.delete' => '삭제',
        'btn.edit' => '편집',
        'auth.login.title' => 'RenalTales 로그인',
        'stories.title' => 'RenalTales 이야기',
        'home.hero.title' => 'RenalTales에 오신 것을 환영합니다',
        'footer.copyright' => '© 2025 RenalTales. 모든 권리 보유.'
    ]],
    
    'ar' => ['Arabic', [
        'nav.home' => 'الرئيسية',
        'nav.stories' => 'القصص',
        'nav.about' => 'عنا',
        'nav.contact' => 'اتصل بنا',
        'nav.login' => 'تسجيل الدخول',
        'nav.register' => 'التسجيل',
        'nav.logout' => 'تسجيل الخروج',
        'nav.profile' => 'الملف الشخصي',
        'btn.save' => 'حفظ',
        'btn.cancel' => 'إلغاء',
        'btn.delete' => 'حذف',
        'btn.edit' => 'تحرير',
        'auth.login.title' => 'تسجيل الدخول إلى RenalTales',
        'stories.title' => 'قصص RenalTales',
        'home.hero.title' => 'مرحباً بك في RenalTales',
        'footer.copyright' => '© 2025 RenalTales. جميع الحقوق محفوظة.'
    ]],
    
    'hi' => ['Hindi', [
        'nav.home' => 'होम',
        'nav.stories' => 'कहानियाँ',
        'nav.about' => 'हमारे बारे में',
        'nav.contact' => 'संपर्क',
        'nav.login' => 'लॉगिन',
        'nav.register' => 'रजिस्टर',
        'nav.logout' => 'लॉगआउट',
        'nav.profile' => 'प्रोफाइल',
        'btn.save' => 'सेव करें',
        'btn.cancel' => 'रद्द करें',
        'btn.delete' => 'डिलीट करें',
        'btn.edit' => 'एडिट करें',
        'auth.login.title' => 'RenalTales में लॉगिन',
        'stories.title' => 'RenalTales कहानियाँ',
        'home.hero.title' => 'RenalTales में आपका स्वागत है',
        'footer.copyright' => '© 2025 RenalTales. सभी अधिकार सुरक्षित।'
    ]],
    
    'th' => ['Thai', [
        'nav.home' => 'หน้าแรก',
        'nav.stories' => 'เรื่องราว',
        'nav.about' => 'เกี่ยวกับเรา',
        'nav.contact' => 'ติดต่อ',
        'nav.login' => 'เข้าสู่ระบบ',
        'nav.register' => 'ลงทะเบียน',
        'nav.logout' => 'ออกจากระบบ',
        'nav.profile' => 'โปรไฟล์',
        'btn.save' => 'บันทึก',
        'btn.cancel' => 'ยกเลิก',
        'btn.delete' => 'ลบ',
        'btn.edit' => 'แก้ไข',
        'auth.login.title' => 'เข้าสู่ระบบ RenalTales',
        'stories.title' => 'เรื่องราว RenalTales',
        'home.hero.title' => 'ยินดีต้อนรับสู่ RenalTales',
        'footer.copyright' => '© 2025 RenalTales. สงวนลิขสิทธิ์ทั้งหมด'
    ]],
    
    'vi' => ['Vietnamese', [
        'nav.home' => 'Trang chủ',
        'nav.stories' => 'Câu chuyện',
        'nav.about' => 'Về chúng tôi',
        'nav.contact' => 'Liên hệ',
        'nav.login' => 'Đăng nhập',
        'nav.register' => 'Đăng ký',
        'nav.logout' => 'Đăng xuất',
        'nav.profile' => 'Hồ sơ',
        'btn.save' => 'Lưu',
        'btn.cancel' => 'Hủy',
        'btn.delete' => 'Xóa',
        'btn.edit' => 'Chỉnh sửa',
        'auth.login.title' => 'Đăng nhập RenalTales',
        'stories.title' => 'Câu chuyện RenalTales',
        'home.hero.title' => 'Chào mừng đến với RenalTales',
        'footer.copyright' => '© 2025 RenalTales. Tất cả quyền được bảo lưu.'
    ]]
];

// Base template (English) to use for missing translations
$baseTemplate = [
    // Navigation
    'nav.home' => 'Home',
    'nav.stories' => 'Stories',
    'nav.about' => 'About',
    'nav.contact' => 'Contact',
    'nav.login' => 'Login',
    'nav.register' => 'Register',
    'nav.logout' => 'Logout',
    'nav.profile' => 'Profile',
    'nav.categories' => 'Categories',
    'nav.write_story' => 'Write Story',
    'nav.moderation' => 'Moderation',
    'nav.manage_users' => 'Manage Users',
    'nav.statistics' => 'Statistics',
    
    // Common actions
    'btn.save' => 'Save',
    'btn.cancel' => 'Cancel',
    'btn.delete' => 'Delete',
    'btn.edit' => 'Edit',
    'btn.view' => 'View',
    'btn.submit' => 'Submit',
    'btn.search' => 'Search',
    'btn.back' => 'Back',
    
    // Authentication
    'auth.login.title' => 'Login to RenalTales',
    'auth.login.subtitle' => 'Welcome back! Please enter your credentials.',
    'auth.login.email' => 'Email Address',
    'auth.login.email_placeholder' => 'Enter your email address',
    'auth.login.password' => 'Password',
    'auth.login.password_placeholder' => 'Enter your password',
    'auth.login.remember' => 'Remember me',
    'auth.login.forgot' => 'Forgot your password?',
    'auth.login.button' => 'Login',
    'auth.login.no_account' => 'No account?',
    'auth.login.register_link' => 'Create one here',
    
    'auth.register.title' => 'Join RenalTales',
    'auth.register.name' => 'Full Name',
    'auth.register.email' => 'Email Address',
    'auth.register.password' => 'Password',
    'auth.register.confirm' => 'Confirm Password',
    'auth.register.agree' => 'I agree to the Terms of Service',
    'auth.login.invalid_credentials' => 'Invalid email or password',
    'auth.login.email_not_verified' => 'Please verify your email address before logging in',
    
    // Stories
    'stories.title' => 'RenalTales Stories',
    'stories.create' => 'Share Your Story',
    'stories.read_more' => 'Read More',
    'stories.category' => 'Category',
    'stories.author' => 'Author',
    'stories.published' => 'Published',
    'stories.tags' => 'Tags',
    
    // Forms
    'form.required' => 'Required field',
    'form.email.required' => 'Email address is required',
    'form.email.invalid' => 'Please enter a valid email address',
    'form.password.required' => 'Password is required',
    'form.password.min' => 'Password must be at least 8 characters',
    'form.password.mismatch' => 'Passwords do not match',
    
    // Messages
    'msg.success.saved' => 'Successfully saved!',
    'msg.success.deleted' => 'Successfully deleted!',
    'msg.error.generic' => 'An error occurred. Please try again.',
    'msg.error.unauthorized' => 'You are not authorized to perform this action.',
    'msg.error.not_found' => 'The requested resource was not found.',
    
    // Footer
    'footer.copyright' => '© 2025 RenalTales. All rights reserved.',
    'footer.privacy' => 'Privacy Policy',
    'footer.terms' => 'Terms of Service',
    'footer.support' => 'Support',
    
    // Home page
    'home.hero.title' => 'Welcome to RenalTales',
    'home.hero.subtitle' => 'A supportive community where people with kidney disorders share their stories, experiences, and hope. Connect with others who understand your journey.',
    'home.hero.read_stories' => 'Read Stories',
    'home.hero.join_community' => 'Join Community',
    'home.hero.share_story' => 'Share Your Story',
    'home.stats.stories_shared' => 'Stories Shared',
    'home.stats.community_members' => 'Community Members',
    'home.stats.comments_support' => 'Comments & Support',
    'home.stats.languages_supported' => 'Languages Supported',
    'home.featured_stories.title' => 'Featured Stories',
    'home.featured_stories.subtitle' => 'Highlighted stories that inspire and connect our community',
    'home.featured_stories.view_all' => 'View All Featured',
    'home.recent_stories.title' => 'Recent Stories',
    'home.recent_stories.subtitle' => 'Latest stories from our community members',
    'home.recent_stories.view_all' => 'View All Stories',
    'home.categories.title' => 'Explore by Category',
    'home.categories.subtitle' => 'Discover stories that resonate with your experience',
    'home.featured' => 'Featured',
];

// Create language files
$i18nDir = dirname(__DIR__) . '/i18n';

foreach ($languages as $code => [$name, $translations]) {
    $filePath = $i18nDir . '/' . $code . '.php';
    
    // Skip if file already exists
    if (file_exists($filePath)) {
        echo "Skipping $code ($name) - file already exists\n";
        continue;
    }
    
    // Merge translations with base template
    $finalTranslations = array_merge($baseTemplate, $translations);
    
    $content = "<?php\n/**\n * $name Language File for RenalTales\n * Contains all translatable strings in $name\n */\n\nreturn [\n";
    
    $currentSection = '';
    foreach ($finalTranslations as $key => $value) {
        $section = explode('.', $key)[0];
        if ($section !== $currentSection) {
            if ($currentSection !== '') {
                $content .= "\n";
            }
            $sectionNames = [
                'nav' => 'Navigation',
                'btn' => 'Common actions',
                'auth' => 'Authentication',
                'stories' => 'Stories',
                'form' => 'Forms',
                'msg' => 'Messages',
                'footer' => 'Footer',
                'home' => 'Home page'
            ];
            $sectionName = $sectionNames[$section] ?? ucfirst($section);
            $content .= "    // $sectionName\n";
            $currentSection = $section;
        }
        
        $content .= "    '$key' => '" . addslashes($value) . "',\n";
    }
    
    $content .= "];\n";
    
    file_put_contents($filePath, $content);
    echo "Generated $code ($name)\n";
}

echo "Language file generation complete!\n";
