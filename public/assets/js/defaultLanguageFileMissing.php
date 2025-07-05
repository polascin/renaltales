<?php if ($defaultLangMissing): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            alert(
                "Default language file is missing!\n" +
                "Timestamp: <?php echo date('Y-m-d H:i:s T'); ?>\n" +
                "Aborting execution.\n" +
                "Please contact the administrator."
            );
        });
    </script>
<?php endif; ?>
</head>
<body>
    <hr>
    <h1>Fatal Error</h1>
    <h2><?= APP_TITLE ?> - Default Language File Missing</h2>
    <p>
        The default language file is missing.<br>
        Timestamp: <?php echo date('Y-m-d H:i:s T'); ?><br>
        Please contact the administrator to resolve this issue.
    </p>
    <hr>
    <h1>Závažná chyba</h1>
    <h2><?= APP_TITLE ?> - Chýba predvolený jazykový súbor</h2>
    <p>
        Chýba predvolený jazykový súbor. Ak chcete tento problém vyriešiť, kontaktujte administrátora.
    </p>
    <hr
</body>
</html>
<?php exit; ?>