( function( $ ) {

    $(document).ready(function() {

        $('.contact_form input.form_submit').on('click' , function (e) {
            e.preventDefault();

            var _this = $(this).closest('.contact_form');
            var datastring = _this.serialize();

            var data = {
                action : 'contact_form_save_data',
                data : datastring
            };

            jQuery.post( ajax_object.ajaxurl, data, function(response) {

                $('#cfs_info').html(response).removeClass('hidden');

                //reset form
                $(".contact_form")[0].reset();
            });

        });




    });

} )( jQuery );

