<?php
add_theme_support('title-tag');
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_action( 'wp_enqueue_scripts', 'greenhead_scripts' );
function greenhead_scripts() {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('style-css', get_stylesheet_directory_uri(). '/style.css',null, $version);
    wp_enqueue_style('gf-css', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700,700i&display=swap',null,'1.0');
    if(!is_page('iframe')) {
        wp_enqueue_script('site', get_stylesheet_directory_uri().'/site.js', array('jquery'), $version, true);
        wp_localize_script( 'site', 'greenhead', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    } else {
        wp_register_style('gh-slick',   get_stylesheet_directory_uri(). '/slick-1.8.1/slick/slick.css', null, '1.8.1');
        wp_register_script('gh-slick',  get_stylesheet_directory_uri(). '/slick-1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
        wp_register_script('gh-embed', get_stylesheet_directory_uri().'/embed.js', array('jquery','gh-slick'), $version, true);
        wp_enqueue_style('gh-slick');
        wp_enqueue_script('gh-slick');
        wp_enqueue_script('gh-embed');
    } 

    if(is_singular('location')) {
        $location =  get_post_meta(get_the_ID(), '_location_id', true); 
        $location_details = greenhead_severity_chart($location);
        wp_enqueue_script('charts', get_stylesheet_directory_uri().'/chart.umd.min.js', array(), '4.5.1', true);
        wp_enqueue_script('gh-location', get_stylesheet_directory_uri().'/location.js', array('charts'), $version, true);
        wp_localize_script( 'gh-location', 'gh_location', array(
            'location' => $location,
            'labels' => $location_details['labels'],
            'datasets' => $location_details['datasets'],
        ));
         
    }
}

//add_action( 'gform_after_submission_1', 'record_user_submission', 10, 2 );
function record_user_submission( $entry, $form ) {
    $post_id = $entry['2'];
    $answer = $entry['1'];
    $date = date("Y-m-d H:i:s");
    if($answer == 'Yes') {
        add_post_meta($post_id,'greenhead_sighting_yes',$date);
    } else {
        add_post_meta($post_id,'greenhead_sighting_no',$date);
    }
    
	GFAPI::delete_entry( $entry['id'] );
}

function show_gforms_data($location) {
	// General search criteria
    $search_criteria = array();

    // start / end date set to yesterday (cron runs at midnight, entries would be from prev day)
    $start_date = date('Y-m-d', strtotime('-7 days'));
    $end_date = date('Y-m-d',strtotime('1 day'));

    //Set gravity forms search range
    $search_criteria['status'] = 'active';
    $search_criteria['start_date'] = $start_date;
    $search_criteria['end_date'] = $end_date;
    $search_criteria['field_filters'][] = array( 'key' => '5', 'value' => $location );
    $form_id = 1;
    $entries = GFAPI::get_entries( $form_id , $search_criteria, null, array( 'offset' => 0, 'page_size' => 99999 ));
    $yes = 0;
    $no = 0;
    $last_reported = '';
    $totalentries = count($entries);
    if($totalentries == 1) {
        $r = ' report';
    } else {
        $r = ' reports';
    }
    $severity = 0;
    if($totalentries > 0) {
        foreach ($entries as $entry) {
            //echo $entry[1];
            if( $last_reported == '') {
                $date = new DateTime($entry['date_created']);
				$date->setTimezone(new DateTimeZone('America/New_York'));
                $last_reported = $date->format('l, F j.');
            }
            
            //echo $last_reported .' ';
            //print_r($entry);
            $answer = $entry[1];
            if($answer == 'Yes') {
                $yes++;
            } else {
                $no++;
            }
            
            $severity += $entry[6];
        }
        //echo $severity;
        if($yes >= $no) {
            $content .= '<p><span class="answer">Yes</span> Our users report a likelihood  you will encounter a greenhead today.</p>';
            $reported = true;
        } else {
            $content .= '<p><span class="answer">No</span> Our users report it is  unlikely you encounter a greenhead today.</p>';
            $reported = false;
        }
        if($severity == 0) {
            $average = 0;
        } else {
            $average = round($severity / $totalentries);
        }
        
        switch ($average) {
            case 0:
                $avg = 'Not a fly in sight';
                break;
            case 1:
                $avg = 'Not bad at all';
                break;
            case 2:
                $avg = 'A little annoying';
                break;
            case 3:
               $avg = 'I debated leaving';
                break;
            case 4:
                $avg = 'HOLY CRAP';
        }
        $content .= '<p>'.$totalentries.$r.' in last 7 days.<br>Average Report: '.$average.'/4, '.$avg.'.<br>Last reported '.$last_reported.'</p>';
    } else {
        $content .= '<p><span class="answer">No</span> We have no reports of greenheads this week, so we assume you are unlikely to encounter a greenhead.</p>';
        $reported = false;
        $content .= '<p>0 reports</p>';
    }
    
    if($reported === false) {
        //no entries in last 7 days
        $now = strtotime(date("Y-m-d H:i:s"));
        $year = date("Y");
        $seasonStart = strtotime("$year-07-01 12:00:00");
        $seasonEnd = strtotime("$year-08-15 12:00:00");
        if($now > $seasonStart && $now < $seasonEnd) {
            $content .= '<p>However, based on the date, we think <span class="answer inline">Yes</span> you are likely to encounter a greenhead, we just haven&rsquo;t received any reports.</p>';
        } else {
            //echo '<p>Based on the date, we suggest that <span class="answer">No</span> you are unlikely to encounter a greenhead today.</p>'; 
        } 
    }
    
    
    
    //last reported comparison
    $date = new DateTime($last_reported);
    $result = $date->format('l, F j, Y');
    $diff = $now - strtotime($last); 
    // 1 day = 24 hours 
    // 24 * 60 * 60 = 86400 seconds 
    $differenceindays = abs(round($diff / 86400)); 
    if($differenceindays == 1) {
        $d = ' day ';
    } else {
        $d = ' days ';
    }
    return $content;
}

function get_greenhead_reports_bk() {
    $report = '';
    $report .= '<div class="columns">';
        $report .= '<div class="column"><h2>Plum Island (Point)</h2>'.show_gforms_data(1).'</div>';
        $report .= '<div class="column"><h2>Plum Island (Refuge)</h2>'.show_gforms_data(2).'</div>';
    $report .= '</div><div class="columns">';
        $report .= '<div class="column"><h2>Salisbury Beach (Line)</h2>'.show_gforms_data(3).'</div>';
        $report .= '<div class="column"><h2>Salisbury Beach (Reservation)</h2>'.show_gforms_data(6).'</div>';
    $report .= '</div><div class="columns">';
        $report .= '<div class="column"><h2>Crane Beach Ipswich</h2>'.show_gforms_data(5).'</div>';
        $report .= '<div class="column"><h2>Hampton Beach</h2>'.show_gforms_data(4).'</div>';
    $report .= '</div><div class="columns">';
        $report .= '<div class="column"><h2>Camp Ellis, Maine</h2>'.show_gforms_data(7).'</div>';
    $report .= '</div>';
    wp_send_json(
        array(
            'reports' => $report
        )
    );
    wp_die();
}

//add_filter('gh_line_item_start_data', 'gh_all_year_report');
function gh_all_year_report($time) {
    if(is_singular('location')) {
        $time = date('05-01-Y');
    }
    return $time;
}
function get_green_location_lineitem($location, $name, $show_asterisk = true, $show_details = true) {
	// General search criteria
    $search_criteria = array();

    // start / end date set to yesterday (cron runs at midnight, entries would be from prev day)
    $start_date = apply_filters('gh_line_item_start_data', date('Y-m-d', strtotime('-5 days')));
    $end_date = date('Y-m-d',strtotime('1 day'));

    //Set gravity forms search range
    $search_criteria['status'] = 'active';
    $search_criteria['start_date'] = $start_date;
    $search_criteria['end_date'] = $end_date;
    $search_criteria['field_filters'][] = array( 'key' => '5', 'value' => $location );
    $form_id = 1;
    
    $entries = GFAPI::get_entries( $form_id , $search_criteria, null, array( 'offset' => 0, 'page_size' => 99999 ));
    $yes = 0;
    $no = 0;
    $last_reported = '';
    $totalentries = count($entries);
    date_default_timezone_set('UTC');
    $current_time = current_time('timestamp', true);
    if($totalentries == 1) {
        $r = ' report';
    } else {
        $r = ' reports';
    }
    $severity = 0;
    $found_outlier = false;
    $details = array();
    if($totalentries > 0) {
        foreach ($entries as $entry) {
            //echo $entry[1];
            
            if( $last_reported == '') {
                $last_reported = strtotime($entry['date_created']);
                $last_dat = $entry['date_created'];
                /*$date = new DateTime($entry['date_created']);
				$date->setTimezone(new DateTimeZone('America/New_York'));
                $last_reported = $date->format('l, F j.');*/
            }
            
            //echo $last_reported .' ';
            //print_r($entry);
            $answer = $entry[1];
            if($answer == 'Yes') {
                $yes++;
            } else {
                $no++;
            }
            
            $severity += $entry[6];
            $details[] = array(
                'date_created' => strtotime($entry['date_created']),
                'severity' => $entry[6],
            );
        }
        //echo $severity;
        if($yes >= $no) {
            $likelihood = 'Yes';
            //$content .= '<p><span class="answer">Yes</span> Our users report a likelihood  you will encounter a greenhead today.</p>';
            $reported = true;
        } else {
            $likelihood = 'No';
            //$content .= '<p><span class="answer">No</span> Our users report it is  unlikely you encounter a greenhead today.</p>';
            $reported = false;
        }
        if($severity == 0) {
            $average = 0;
        } else {
            $average = round($severity / $totalentries);
        }
        
        $avg = get_average($average);
        
        $average = $average .'/4, '.$avg;

        $total_reports = $totalentries;
        $last_reported_time = sprintf('%s ago', human_time_diff($last_reported, $current_time));
    } else {
        $total_reports = 0;
        $average = 'N/A';
        $reported = false;
        $last_reported_time = '5+ days ago';
        $likelihood = 'No';
    }
    
    if($reported === false) {
        //no entries in last 7 days
        $now = strtotime(date("Y-m-d H:i:s"));
        $year = date("Y");
        $seasonStart = strtotime("$year-07-01 12:00:00");
        $seasonEnd = strtotime("$year-08-15 12:00:00");
        if($now > $seasonStart && $now < $seasonEnd) {
            $found_outlier = true;
            $likelihood = 'Yes';
            if($show_asterisk) {
                $likelihood .= '*';
            }
            //$content .= '<p>However, based on the date, we think <span class="answer inline">Yes</span> you are likely to encounter a greenhead, we just haven&rsquo;t received any reports.</p>';
        } else {
            //echo '<p>Based on the date, we suggest that <span class="answer">No</span> you are unlikely to encounter a greenhead today.</p>'; 
        } 
    }
    
    
    $detail_count = 1;
    if(!empty($details)) {
        $detail_count = count($details);
    }
    $response = '<div data-labelss="Location" class="location-name">'.$name;
        if($show_details) {
            if($detail_count > 1) {
                $response .= ' <span class="toggle-gh-details"><span class="desktop-only">+</span><span class="mobile-only">Show Report Details</span></span>';
            }
        }
    $response .= '</div>';
    $response .= '<div data-label="Last reported" class="last-reported">'.$last_reported_time.'</div>';
    $response .= '<div data-label="Reports" class="total-reports">'.$total_reports.'</div>';
    $response .= '<div data-label="Severity" class="avg">'.$average.'</div>';
    $response .= '<div data-label="In season?" class="likelihood">'.$likelihood.'</div>';
    
    if($show_details) {
        if($detail_count > 1) {
            $response .= '<div class="gh-details hide-details">';
                $response .= '<div class="mobile-only reporting-details-heading">Report Details:</div>';
                $response .= '<div class="gh-report">';
                    foreach($details as $detail) {
                        $last_reported_time = sprintf('%s ago', human_time_diff($detail['date_created'], $current_time));
                        //$report_time = date('g:i a',$detail['date_created']); //, '.$report_time.'
                        $response .= '<div class="desktop-only"></div><div class="detail-report-time">'.$last_reported_time.'<span class="mobile-only">: </span></div><div class="desktop-only"></div><div class="detail-report-average">'.get_average($detail['severity']).'</div><div class="desktop-only"></div>';
                    }
                $response .= '</div>';
            $response .= '</div>';
        }
    }
    
    
    
    return array('response' => $response, 'outlier' => $found_outlier);
}

function get_average($average) {
    switch ($average) {
        case 0:
            $avg = 'Not a fly in sight';
            break;
        case 1:
            $avg = 'Not bad at all';
            break;
        case 2:
            $avg = 'A little annoying';
            break;
        case 3:
            $avg = 'I debated leaving';
            break;
        case 4:
            $avg = 'HOLY CRAP';
    }
    return $avg;
}

function get_greenhead_location_options() {
    return array(
        1 => 'Plum Island (Point)',
        2 => 'Plum Island (Refuge)',
        3 => 'Salisbury (Line)',
        6 => 'Salisbury (Reservation)',
        5 => 'Crane Beach, Ipswich',
        4 => 'Hampton Beach',
        7 => 'Camp Ellis, Maine',
        8 => 'Sandy Neck, Barnstable',
        9 => 'Nauset Beach, Orleans',
        10 => 'Wingaersheek Beach, Gloucester'
    );
}
function get_greenhead_reports() {
    date_default_timezone_set(wp_timezone_string());
    $report = '<div class="today">'.date('l, F jS',strtotime('today')).'</div>';
    $locations = get_greenhead_location_options();
    $report .= '<div class="gh-report">';
        $report .= get_report_columns();
        
        
    $found_outlier = false;
    foreach($locations as $index => $name) {
        $data = get_green_location_lineitem($index, $name);
        $report .= $data['response'];
        if(!$found_outlier) {
            $found_outlier = $data['outlier'];
        }
    }
    $report .= '</div>';
    $report .= '<div style="margin-top: 40px; font-size: .7em; text-align: left;">Report count is a total of the last 5 days, severity is an average of those reports.</div>';
    if($found_outlier) {
        $report .= '<div style="margin-top: 10px; font-size: .7em; text-align: left;"><sup>*</sup>Based on the time of year, rather than reported data.</div>';
    }
    wp_send_json(
        array(
            'reports' => $report
        )
    );
    wp_die();
}
add_action("wp_ajax_get_greenhead_reports", "get_greenhead_reports");
add_action("wp_ajax_nopriv_get_greenhead_reports", "get_greenhead_reports");

function get_report_columns() {
    $report = '';
    $report .= '<div class="desktop-only font-bold">Location</div>';
    $report .= '<div class="desktop-only font-bold">Last reported</div>';
    $report .= '<div class="desktop-only font-bold">Reports</div>';
    $report .= '<div class="desktop-only font-bold">Severity</div>';
    $report .= '<div class="desktop-only font-bold likelihood">In season?</div>';
    return $report;
}
function greenhead_embed_builder() {
    $locations = get_greenhead_location_options();
    $form = '<form class="embed-builder">';
    $form .= '<p>Location(s)</p>';
    $form .= '<div class="grid">';
    foreach($locations as $id => $location) {
        $form .= '<label><input type="checkbox" value="'.$id.'" name="locations[]" /> '.$location.'</label>';
    }
    $form .= '</div>';

    //fields
    /*$fields = array(
        'date' => "Today's Date",
        'location' => 'Location',
        'reports' => 'Reports',
        'last_reported' => 'Last reported',
        'severity' => 'Severity',
        'in_season' => 'In season?',
    );
    $form .= '<p>Field(s)</p>';
    $form .= '<div class="grid grid-3">';
    foreach($fields as $value => $name) {
        $form .= '<label><input type="checkbox" value="'.$value.'" name="fields[]" checked /> '.$name.'</label>';
    }
    $form .= '</div>';*/

    //fields
    $sizes = array(
        'small_card' => 'Small card',
        'large_card' => 'Large card',
        'full_width' => 'Full width',
    );
    $form .= '<p>Display Size</p>';
    $form .= '<div class="grid grid-3">';
    $sizect = 0;
    foreach($sizes as $value => $name) {
        $checked = '';
        if($sizect == 0) {
            $checked = 'checked';
        }
        $form .= '<label><input type="radio" value="'.$value.'" name="size" '.$checked.' /> '.$name.'</label>';
        $sizect++;
    }
    $form .= '</div>';

    $form .= '<input type="submit" value="Generate embed code" />';
    $form .= '</form>';
    $form .= '<div id="embed-code"></div>';
    return $form;
}

function build_embed_code() {
    //$fields = $_GET['fields'];
    $locations = $_GET['locations'];
    $size = sanitize_text_field($_GET['size']);
    $width = '300';
    $height = '160';
    if($size == 'large_card') {
        $width = '300';
        $height = '400';
    } else if($size == 'full_width') {
        $width = '100%';
        $height = '200';
    }
    $html = '<iframe id="greenhead-report" class="'.$size.'" src="'.trailingslashit(get_bloginfo('url')).'iframe/?location='.urlencode(implode(',',$locations)).'&size='.$size.'" width="'.$width.'" height="'.$height.'" style="border: none;"></iframe>'; //&fields='.urlencode(implode(',',$fields)).'
    $embed = '<script src="https://greenheadreport.com/embed.js?ver=1.0" data-locations="'.implode(',',$locations).'" data-size="'.$size.'"></script><div id="greenhead-report"></div>';
    //$message = 'Code generated! Locations: '.implode(', ', $locations).'. Size: '.$size;
    //wp_mail('hello@mynameisgregg.com','Greenhead code generated', $message);
    wp_send_json( array(
        'preview' => $html,
        'html' => '<div><p class="embed-code-text">Embed code:</p><div class="embed-code"><code>'.htmlspecialchars($embed).'</code></div><button id="copy-embed-code" class="btn">Copy embed code</button></div>'
    ));
}
add_action("wp_ajax_build_embed_code", "build_embed_code");
add_action("wp_ajax_nopriv_build_embed_code", "build_embed_code");

function footer_text_line_1() {
    echo '<p class="copy">&copy; '.date('Y').' The Greenhead Report. Built by <a href="https://mynameisgregg.com" target="_blank">Gregg Hogan</a> and powered by users like you.</p>';
}
function footer_text_line_2() {
    echo '<p class="copy" style="margin-top: 10px;">Have a question, comment or beach suggestion? Email <a href="mailto:hello@mynameisgregg.com" title="Email us">hello@mynameisgregg.com</a>.';
}

function gh_meta_boxes() {
    $page_by_path = get_page_by_path('iframe');
    if($page_by_path !== null) {
        $post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
        if ($post_id == $page_by_path->ID){
            add_meta_box( 'gh-embed-locations', __( 'Embed locations', 'textdomain' ), 'embed_display_locations_callback', 'page' );
        }
    }
}
add_action('add_meta_boxes','gh_meta_boxes');

function embed_display_locations_callback( $post ) {
	$history = get_post_meta($post->ID,'embed_locations',true);
    if(is_array($history)) {
        echo '<table style="text-align: left; width: 100%;" cellpadding="5"><tr><th>Location</th><th>Path</th><th>Count</th></tr>';
        foreach($history as $url => $data) {
            $urlshown = false;
            foreach($data as $path => $count) {
                echo '<tr><td>';
                if(!$urlshown) {
                    echo '<a href="'.$url.'" target="_blank">'.$url.'</a>';
                    $urlshown = true;
                }
                echo '</td><td>'.$path.'</td><td>'.$count.'</td></tr>';
            }
        }
        echo '</table>';
    } else {
        echo '<p>No embeds captured</p>';
    }
}
function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

function gh_register_cpt_and_taxonomies() {

	/**
	 * Locations
	 */
	$args = array(
		"label" => __( "All Locations", "" ),
		"labels" => array(
			"name" => __( "Locations", "" ),
			"singular_name" => __( "Location", "" ),
		),
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		'show_in_rest' => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"rewrite" => array( "slug" => 'location', "with_front" => false ),
		"query_var" => true,
		"supports" => array( "title", "custom-fields", "thumbnail", "author", "page-attributes", "post-formats" ),
		"taxonomies" => array( ),
		'menu_icon' => 'dashicons-marker',
	);

	register_post_type( "location", $args );
}
add_action( 'init', 'gh_register_cpt_and_taxonomies' );

add_action('add_meta_boxes', function () {
    add_meta_box(
        'location_id_metabox',
        'Location ID',
        'render_location_id_metabox',
        'location', // Change to your CPT
        'side',
        'default'
    );
});

function render_location_id_metabox($post) {
    wp_nonce_field('save_location_id_metabox', 'location_id_metabox_nonce');

    $location_id = get_post_meta($post->ID, '_location_id', true);
    ?>
    <p>
        <label for="location_id">GF Location ID</label>
        <input
            type="number"
            id="location_id"
            name="location_id"
            value="<?php echo esc_attr($location_id); ?>"
            min="0"
            step="1"
            style="width:100%;"
        />
    </p>
    <?php
}

add_action('save_post', function ($post_id) {

    if (
        !isset($_POST['location_id_metabox_nonce']) ||
        !wp_verify_nonce($_POST['location_id_metabox_nonce'], 'save_location_id_metabox')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['location_id']) && $_POST['location_id'] !== '') {
        update_post_meta(
            $post_id,
            '_location_id',
            absint($_POST['location_id'])
        );
    } else {
        delete_post_meta($post_id, '_location_id');
    }
});


function greenhead_severity_chart($location) {
	if (!class_exists('GFAPI')) {
		return '';
	}

	$atts = array(
		'start'       => '06-01',
		'end'         => '08-15',
		'rolling'     => 7,
	);

	$form_id     = 1;
	$location_id = absint($location);
	$rolling     = absint($atts['rolling']);

	if (!$form_id || !$location_id) {
		return '';
	}

	// Gravity Forms field IDs from your export:
	// Location = 5, Severity = 6
	$location_field_id = '5';
	$severity_field_id = '6';

	$search_criteria = [
		'status'        => 'active',
		'field_filters' => [
			[
				'key'   => $location_field_id,
				'value' => (string) $location_id,
			],
		],
	];

	$paging = [
		'offset'    => 0,
		'page_size' => 5000,
	];

	$entries = GFAPI::get_entries($form_id, $search_criteria, null, $paging);

	if (is_wp_error($entries) || empty($entries)) {
		return '<p>No reports found for this location yet.</p>';
	}

	$daily = [];

	foreach ($entries as $entry) {
		if (empty($entry['date_created']) || !isset($entry[$severity_field_id])) {
			continue;
		}

		$timestamp = strtotime($entry['date_created']);
        $year = date('Y', $timestamp);

        if ($year < 2023) {
            continue;
        }
		$year      = date('Y', $timestamp);
		$month_day = date('m-d', $timestamp);

		if ($month_day < $atts['start'] || $month_day > $atts['end']) {
			continue;
		}

		$severity = is_numeric($entry[$severity_field_id]) ? (float) $entry[$severity_field_id] : null;

		if ($severity === null) {
			continue;
		}

		if (!isset($daily[$year][$month_day])) {
			$daily[$year][$month_day] = [];
		}

		$daily[$year][$month_day][] = $severity;
	}

	if (empty($daily)) {
		return '<p>No reports found for this date range.</p>';
	}

	// Average each day.
	$averages = [];

	foreach ($daily as $year => $days) {
		foreach ($days as $month_day => $values) {
			$averages[$year][$month_day] = array_sum($values) / count($values);
		}
	}

	ksort($averages);

	$labels = [];
	$start  = strtotime('2024-' . $atts['start']);
	$end    = strtotime('2024-' . $atts['end']);

	for ($time = $start; $time <= $end; $time = strtotime('+1 day', $time)) {
		$month_day = date('m-d', $time);
		$labels[] = [
			'key'   => $month_day,
			'label' => date('M j', $time),
		];
	}

	$datasets = [];

	foreach ($averages as $year => $days) {
		$data = [];

		foreach ($labels as $label) {
			$month_day = $label['key'];
			$data[] = $days[$month_day] ?? null;
		}

		if ($rolling > 1) {
			$data = greenhead_rolling_average($data, $rolling);
		}

		$datasets[] = [
            'label'       => $year,
            'data'        => $data,
            'tension'     => 0.25,
            'spanGaps'    => true,
            'pointRadius'      => 0,
            'pointHoverRadius' => 0,
            'hitRadius'        => 8,
            'borderWidth'      => 2,
        ];
	}
    
    $chart_labels = array_map(function($label) {
        return $label['label'];
    }, $labels);

	return array(
        'labels' => $chart_labels,
        'datasets' => $datasets
    );
}

function greenhead_rolling_average($data, $window = 7) {
	$output = [];
	$count  = count($data);

	for ($i = 0; $i < $count; $i++) {
		$values = [];

		$start = max(0, $i - floor($window / 2));
		$end   = min($count - 1, $i + floor($window / 2));

		for ($j = $start; $j <= $end; $j++) {
			if ($data[$j] !== null) {
				$values[] = $data[$j];
			}
		}

		$output[] = !empty($values)
			? round(array_sum($values) / count($values), 2)
			: null;
	}

	return $output;
}
?>