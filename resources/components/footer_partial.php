<?php
/**
 * Footer Partial Component
 * 
 * Simple footer template partial
 */
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-content">
            <p class="copyright">
                &copy; <?= esc_html($page_footer['year'] ?? date('Y')) ?> 
                <?= esc_html($page_footer['copyright'] ?? 'RenalTales') ?>
            </p>
            
            <nav class="footer-links">
                <a href="/privacy">Privacy Policy</a>
                <a href="/terms">Terms of Service</a>
                <a href="/contact">Contact</a>
            </nav>
        </div>
    </div>
</footer>
