<?php get_header();
    
while ( have_posts() ){
    the_post();

    the_content();

    echo '<h2 class="line"><span>The <div class="green">&nbsp;Greenhead </div> Report</span></h2>';
    echo '<div id="greenhead-reports-summary"><div class="opaque today">Loading report...</div></div>';

    echo '<div class="gh-spacer"></div>';
    echo '<div class="gh-spacer"></div>';
    
    echo '<p>Thank you to everyone who continues to contribute to the report.<br>';
    echo "2024 brought in 500+ reports; this tiny project's most successful year.<br>";
    echo 'If you have suggestions for improving the report for 2025 and beyond, don&rsquo;t hesitate to <a href="mailto:hello@mynameisgregg.com">reach out</a>!</p>';

    echo '<div class="gh-spacer"></div>';
    echo '<h2 class="line"><span>Embed <div class="green">&nbsp;The Greenhead Report on your website!</div></span></h2>';
    echo '<p>Select your location(s) and display settings to generate an embeddable code for your website.</p>';
    echo greenhead_embed_builder();
    echo '<div class="gh-spacer"></div>';
}
get_footer(); ?>