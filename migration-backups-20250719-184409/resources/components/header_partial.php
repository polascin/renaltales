<?php
/**
 * Header Partial Component
 * 
 * Simple header template partial
 */
?>
<header class="site-header">
    <div class="header-container">
        <h1 class="site-title">
            <a href="/"><?= esc_html($page_meta['app_name'] ?? 'RenalTales') ?></a>
        </h1>
        
        <nav class="main-navigation">
            <ul class="nav-menu">
                <li><a href="/"><?= esc_html($page_navigation['home'] ?? 'Home') ?></a></li>
                <li><a href="/stories"><?= esc_html($page_navigation['stories'] ?? 'Stories') ?></a></li>
                <li><a href="/community"><?= esc_html($page_navigation['community'] ?? 'Community') ?></a></li>
                <li><a href="/about"><?= esc_html($page_navigation['about'] ?? 'About') ?></a></li>
            </ul>
        </nav>
        
        <?php if (isset($page_languages)): ?>
        <div class="language-switcher-container">
            <?php include __DIR__ . '/language_switcher_partial.php'; ?>
        </div>
        <?php endif; ?>
    </div>
</header>
