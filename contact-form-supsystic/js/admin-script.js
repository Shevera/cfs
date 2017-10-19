( function( $ ) {

    $(document).ready(function() {

        $('.contact_form_setting input[type="submit"]').on('click' , function (e) {
            e.preventDefault();

            var $input1 = $('input#cfs_to_whom').val();
            var $input2 = $('input#cfs_from_whom').val();
            var $input3 = $('input#cfs_subject').val();
            var $textarea = $('textarea#cfs_message').val();

            var data = {
                action : 'setting_save',
                cfs_to_whom : $input1 ,
                cfs_from_whom : $input2,
                cfs_subject : $input3,
                cfs_message : $textarea
            };

            jQuery.post( ajax_object.ajaxurl, data, function(response) {
                $('#cfs_info').html(response).removeClass('hidden');
            });

        });

        //meta box drag and drop
        $('.metabox_submit').click(function(e) {
            e.preventDefault();
            $('#publish').click();
        });
        $('#add-row').on('click', function() {
            var row = $('.empty-row.screen-reader-text').clone(true);
            row.removeClass('empty-row screen-reader-text');
            row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
            return false;
        });
        $('.remove-row').on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
        if (document.getElementById("#repeatable-fieldset-one") !== null) {
            $('#repeatable-fieldset-one tbody').sortable({
                opacity: 0.6,
                revert: true,
                cursor: 'move',
                handle: '.sort'
            });
        }
        //meta box drag and drop end
    });

} )( jQuery );

