<!DOCTYPE html>
<html>
<head>
  <?php if(!is_page('iframe')) { ?>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-2FZSE06H2R"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}

    // Default to no analytics cookies until the user decides.
    gtag('consent', 'default', {
     analytics_storage: 'denied',
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied',
			functionality_storage: 'denied',
			personalization_storage: 'denied',
      wait_for_update: 500
    });

    gtag('js', new Date());

    gtag('config', 'G-2FZSE06H2R', {
      'allow_google_signals': false,
      'allow_ad_personalization_signals': false
    });
  </script>
  <?php } ?>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body>
    <div class="container">
        <div class="container-content">
            <div class="logo-container">
              <a href="<?php echo get_bloginfo('url'); ?>" title="Greenhead Report">
                  <img src="https://greenheadreport.com/wp-content/uploads/2022/06/head-min.png" class="greenheadlogo" title="A lil stupid greenhead" />
                  <?php if(!is_singular('location')) { ?>
                    <h1>Is it Greenhead Season?</h1>
                  <?php } ?>
              </a>
            </div>