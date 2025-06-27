(function($) {
    jQuery(document).ready(function() {
        var data = {
            'action': 'get_greenhead_reports',
        };
        $.ajax({
            url: greenhead.ajaxurl,
            type: 'get',
            data: data,
            success: function(response){
                $('#greenhead-reports-summary').html(response.reports);
            }
        }); 

        function resize_embed() {
            
            if($('#greenhead-report').length) {
                if($('#greenhead-report').hasClass('full_width')) {
                    var windowWidth = parseInt($(document).width());    
                    
                    if(windowWidth >= 768) {
                        $('#greenhead-report').css('height','200px');
                    } else {
                        $('#greenhead-report').css('height','400px');
                    }
                }
            }
        }

        $(document).on('submit','.embed-builder', function(e) {
            e.preventDefault();
            var locations = [];
            $('input[name="locations[]"]').each(function() {
                if(jQuery(this).is(":checked")){
                    locations.push(this.value);
                }
            });
            if(locations.length == 0) {
                $('#embed-code').html('<div class="embed-error">Please select at least one location.</div>');
                return false;
            }
            /*var fields = [];
            $('input[name="fields[]"]').each(function() {
                if(jQuery(this).is(":checked")){
                    fields.push(this.value);
                }
            });*/
            var size =  $('input[name="size"]:checked').val();
            if(locations.length > 1 && size == 'small_card') {
                $('#embed-code').html('<div class="embed-error">Please select only one location for a small card.</div>');
                return false;
            }
            var data = {
                'action': 'build_embed_code',
                'locations' : locations,
                'size' : size
            }; //'fields' : fields,
            $.ajax({
                url: greenhead.ajaxurl,
                type: 'get',
                data: data,
                success: function(response){
                    $('#embed-code').html(response.preview);
                    $('#embed-code').append(response.html);
                    resize_embed();
                }
            }); 
        });

        $(window).on('resize', function() {
            resize_embed();
        });

        $(document).on('click','#copy-embed-code', function(e){
            e.preventDefault();
            var $this = $(this);
            var text = $this.text();
            navigator.clipboard.writeText($('.embed-code').text().trim());
            $this.text('Copied!');
            setTimeout(function() {
                $this.text(text);
            },3000);
        });

        $(document).on('click','.toggle-gh-details',function() {
            if ($(this).text() == '+') {
                $(this).text("-");
            } else {
                $(this).text("+");
            }
            $(this).closest('.location-name') // find the wrapping div
           .nextAll('.gh-details').first() // get the next .gh-details
           .toggleClass('hide-details');
        });

    });

    $(document).bind('gform_post_render', function(){
        var data = {
            'action': 'get_greenhead_reports',
        };
        $.ajax({
            url: greenhead.ajaxurl,
            type: 'get',
            data: data,
            success: function(response){
                $('#greenhead-reports-summary').html(response.reports);
            }
        }); 
     });
})( jQuery );
