<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
<style>.embed,body,html{background:0 0!important}.embed,.embed-content{width:100%;height:100%}.embed{overflow:hidden}.embed-content{border:1px solid #fff;border-radius:6px;box-sizing:border-box;padding:8px 12px;display:flex;flex-direction:column;font-size:90%;overflow:hidden!important;background:#9dc183}.copy,h1,h1 span,h2,h3,h4{padding:0!important}.embed-content.small_card{font-size:75%}.embed-content.small_card .copy{font-size:14px!important}.embed-content .copy{position:relative;z-index:999}.embed-top{flex:1}h1{font-size:115%}.copy,h1,h2,h3,h4{margin:0!important}.gh-report .location-name{font-size:24px!important;margin-top:8px}.gh-report .likelihood{margin-bottom:0!important;font-size:1em!important}.gh-report{gap:0 10px;margin-top:10px}.gh-report div{margin-bottom:2px}.embed-content.small_card .location-name{display:none!important}.gh-report .likelihood:before{font-size:.8em!important}.embed-date{font-weight:100}.embed-content.small_card .embed-date{float:right;font-size:80%;line-height:150%}.embed-content.full_width .embed-date,.embed-content.large_card .embed-date{opacity:1;font-size:70%;display:block}.embed-content.full_width .embed-location .location-name{margin-top:0!important}.embed-content.full_width .embed-location .gh-report.full_width{margin-top:0}</style>
<?php 
if(isset($_GET['embed-url'])) {
  $url = sanitize_url($_GET['embed-url']);
  if($url != '') {
    $path = 'unknown';
    if(isset($_GET['embed-path'])) {
      $path = rtrim(sanitize_text_field($_GET['embed-path']), '/');;
    }
    $post_id = get_the_ID();
    $history = get_post_meta($post_id,'embed_locations',true);
    if(!is_array($history)) {
      $history = array();
    }
    if(!isset($history[$url])) {
      $history[$url] = array();
    }
    if(!isset($history[$url][$path])) {
      $history[$url][$path] = 0;
    }
    $history[$url][$path] = $history[$url][$path] + 1;
    update_post_meta($post_id,'embed_locations',$history);
  }
} ?>
</head>
<body class="embed">
  <?php 
  $size = 'small_card';
  if(isset($_GET['size'])) {
    $size = sanitize_text_field($_GET['size']);
  } ?>
  <div class="embed-content <?php echo $size; ?>">
    <div class="embed-top">
      <h1>Greenhead Report
      <?php date_default_timezone_set(wp_timezone_string());
      if($size != 'small_card') {
        echo '<span class="embed-date">'.date('l, F jS',strtotime('today')).'</span>';
      } else {
        echo '<span class="embed-date">'.date('F jS',strtotime('today')).'</span>';
      }
      echo '</h1>';
      if($size == 'full_width') {
        echo '<div class="gh-report full_width">';
        echo get_report_columns();
        echo '</div>';
      }
      if($size != 'small_card') {
        echo '<div class="embed-locations">';
      }
      if(!isset($_GET['location'])) {
        echo 'An error occurred, no locations specified.';
      } else {
        $locations = explode(',',$_GET['location']);
        $default_locations = get_greenhead_location_options();
        $found_outlier = false;
        foreach($locations as $location) {
          if(isset($default_locations[$location])) {
            if($size == 'small_card') {
              echo '<h4>'.$default_locations[$location].'</h4>';
            } else {
              echo '<div class="embed-location">';
            }
            
            echo '<div class="gh-report">';
            if($size == 'large_card') {
              echo get_report_columns();
            }
            $response = get_green_location_lineitem($location, $default_locations[$location], false);
            echo $response['response'];
            if(!$found_outlier) {
              $found_outlier = $response['outlier'];
            }
            if($size != 'small_card') {
              echo '</div>';
            }
            echo '</div>';
          }
        }
      }
      if($size != 'small_card') {
        echo '</div>';
      } ?>
    
    </div>
    <div class="footer-text">
      <?php /*if($found_outlier) {
        echo '<p class="copy"><sup>*</sup>Based on the time of year, rather than reported data.</p>';
      }*/ ?>
    <?php echo '<p class="copy">&copy; '.date('Y').' <a href="https://greenheadreport.com/" title="The Greenhead Report" target="_parent">The Greenhead Report</a>. Built by <a href="https://mynameisgregg.com" target="_parent">Gregg Hogan</a> and powered by users like you.</p>'; ?>
    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>