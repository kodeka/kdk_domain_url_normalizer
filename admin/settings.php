<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
   <h2>Domain URL Normalizer (by Kodeka)</h2>
   <form method="post" action="options.php">
   <?php
       // This prints out all hidden setting fields
       settings_fields('kdk_domain_url_normalizer_option_group');
       do_settings_sections('kdk-domain-url-normalizer-admin');
       submit_button();
   ?>
   </form>
</div>
