<?php 
/*1 => 'Plum Island (Point)',
2 => 'Plum Island (Refuge)',
3 => 'Salisbury (Line)',
6 => 'Salisbury (Reservation)',
5 => 'Crane Beach, Ipswich',
4 => 'Hampton Beach',
7 => 'Camp Ellis, Maine',
8 => 'Sandy Neck, Barnstable',
9 => 'Nauset Beach, Orleans',
10 => 'Wingaersheek Beach, Gloucester'*/
$location =  get_post_meta(get_the_ID(), '_location_id', true); 
if($location == '') {
    wp_redirect(get_bloginfo('url'));
    exit;
}
get_header();
$locations = get_greenhead_location_options();
$title = $locations[$location];
$location_details = greenhead_severity_chart($location);
echo '<h1>Greenhead Report: '.$title.'</h1>';

echo '<h2>5 Day Report</h2>';
echo '<div class="single-report-data">';
$location_data = get_green_location_lineitem($location, $title);
echo $location_data['response'];
echo '</div>';

if(!empty($location_details)) {
    echo '<h2>Annual Trends</h2>';
    //echo '<p>Based on a 5 Day rolling average</p>';
    echo '<div class="greenhead-severity-chart-wrap">';
    echo '<canvas id="greenhead-severity-chart-' . $location .'"></canvas>';
    echo '</div>';
    echo '<div style="margin-top: 10px; font-size: .7em; text-align: left;">Trends calculated using a 5-day rolling average.</div>';
}
echo do_shortcode('[gravityform id="1" title="false" ajax="true" field_values="location='.$location.'"]');
get_footer();
?>