        <?php footer_text_line_1(); ?>
        <?php footer_text_line_2(); ?>
        <?php 
        echo '<p class="copy" style="margin-top:10px;">
        If you find The Greenhead Report helpful, consider <a href="https://www.buymeacoffee.com/greggoryhogan" target="_blank" title="Buy me a coffee">buying me a coffee</a> to help development costs.</p>';  
        ?>
        <p class="copy" id="analytics-settings" style="margin-top:10px;">Update Cookie Preference</p>
        </div><!--container-content-->
    </div>
    <div id="analytics-consent" class="analytics-consent" hidden>
        <p>
            We use Google Analytics to understand how visitors use the site.
        </p>
        <div class="consent-actions">
            <button type="button" data-analytics-consent="accept">
                Accept
            </button>

            <button type="button" data-analytics-consent="decline">
                Decline
            </button>
        </div>
    </div>
<?php wp_footer(); ?>
</body>
</html>