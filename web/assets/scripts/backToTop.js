'use strict';

(function () {

    var buttonTop = '<a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Click to return on the top page" data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-up"></span></a>';

    $(document).ready(function () {
        $('div.page-content').first().append(buttonTop);

        // Scroll to the top
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.back-to-top').fadeIn();
            } else {
                $('.back-to-top').fadeOut();
            }
        });

        //Click event to scroll to top
        $('.back-to-top').click(function () {
            $('html, body').animate({
                scrollTop: 0
            }, 200);
            return false;
        });

    });
})();