<?php
// Flash Messages Partial
use RenalTales\Core\FlashMessages;
?>

<?php if (FlashMessages::hasAny()): ?>
    <div class="flash-messages">
        <?= FlashMessages::render() ?>
    </div>
<?php endif; ?>

<script>
// Auto-dismiss flash messages after 5 seconds (except permanent ones)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            if (bootstrap && bootstrap.Alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>
