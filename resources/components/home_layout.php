<?php if (!defined('COMPONENT_TEMPLATE')) {
    die('Direct access denied');
} ?>
<!DOCTYPE html>
<html lang="<?= esc_attr($page_meta['language'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc_html($page_meta['title'] ?? 'RenalTales') ?></title>
    <link rel="stylesheet" href="/assets/css/main.css?v=<?= time() ?>">
</head>
<body>
    <?php include __DIR__ . '/header_partial.php'; ?>
    
    <main class="main-content">
        <?php echo render_hero_section($page_hero ?? []); ?>
        <?php echo render_feature_cards($page_features ?? []); ?>
    </main>
    
    <?php include __DIR__ . '/footer_partial.php'; ?>
    
    <script src="/assets/js/main.js"></script>
</body>
</html>
