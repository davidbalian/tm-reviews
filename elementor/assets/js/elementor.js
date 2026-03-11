(function ($) {

    'use strict';

    $(window).on('elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction('frontend/element_ready/templines-moto-slider.default', function ($scope) {
                if (!$('.slick-initialized', $scope).hasClass('templines-moto-slider')) {
                    fl_theme.initMotoSlider();
                }
            });


            elementorFrontend.hooks.addAction('frontend/element_ready/tmreviews-places.default', function ($scope) {

            });

    });

})(jQuery);
