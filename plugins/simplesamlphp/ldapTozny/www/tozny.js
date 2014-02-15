/**
 * Created by kirk on 2/9/14.
 */
function init(tozny_args) {

    $(document).ready(function () {

        if (window.location.hash === '#provision') {
            $('#provision').show();
            $('#login').hide();
            $('#qr_code_login').empty();
        } else {
            $('#provision').hide();
            $('#login').show();
            $('#qr_code_login').tozny(tozny_args);
        }
    });
}