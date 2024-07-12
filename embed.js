(function($) {
    $(document).ready(function() {
        if($('.embed-location').length > 1) {
            var vertical = false;
            if($('.embed-content').hasClass('full_width')) {
                vertical = true;
            }
            $('.embed-locations').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: false,
                dots: false,
                speed: 1000,
                vertical: vertical,
                adaptiveHeight: false,
                responsive: [
                    {
                    breakpoint: 767,
                    settings: {
                        vertical: false
                    }
                    }
                ]
            });    
        }
        
        $('.embed-content.small_card .gh-report').slick({
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            arrows: false,
            dots: false,
            speed: 1000,
        });
    })

})( jQuery );
