<?php get_header();
    
while ( have_posts() ){
    the_post();

    the_content();

    echo '<h2 class="line"><span>The <div class="green">&nbsp;Greenhead </div> Report</span></h2>';
    echo '<div id="greenhead-reports-summary"><div class="opaque today">Loading report...</div></div>';

    echo '<div class="gh-spacer"></div>';
    echo '<div class="gh-spacer"></div>';
    echo '<div class="gh-spacer"></div>';
    echo '<h2 class="line"><span><div class="green">New! </div> Embed <div class="green">&nbsp;The Greenhead Report on your website!</div></span></h2>';
    echo '<p>Select your location(s) and display settings to generate an embeddable code for your website.</p>';
    echo greenhead_embed_builder();
    echo '<div class="gh-spacer"></div>';
   
}
get_footer(); ?>