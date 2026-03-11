jQuery.noConflict()(function ($) {

    "use strict";



    //jQuery time
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches

    $(".next").click(function () {
        if (animating) return false;
        animating = false;

        current_fs = $(this).parent();
        next_fs = $(this).parent().next();

        //activate next step on progressbar using the index of next_fs
        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

        //show the next fieldset
        next_fs.show();
        //hide the current fieldset with style
        current_fs.animate({
            opacity: 0
        }, {
            step: function (now, mx) {
                //as the opacity of current_fs reduces to 0 - stored in "now"
                //1. scale current_fs down to 80%
                scale = 1 - (1 - now) * 0.2;
                //2. bring next_fs from the right(50%)
                left = (now * 50) + "%";
                //3. increase opacity of next_fs to 1 as it moves in
                opacity = 1 - now;
                current_fs.css({
                    'transform': 'scale(' + scale + ')',
                    'position': 'relative'
                });
                next_fs.css({
                    'left': left,
                    'opacity': opacity
                });
            },
            duration: 0,
            complete: function () {
                current_fs.hide();
                animating = false;
            },
            //this comes from the custom easing plugin
            //easing: 'easeInOutBack'
        });
    });

    $(".previous").click(function () {
        if (animating) return false;
        animating = false;

        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();

        //de-activate current step on progressbar
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

        //show the previous fieldset
        previous_fs.show();
        //hide the current fieldset with style
        current_fs.animate({
            opacity: 0
        }, {
            step: function (now, mx) {
                //as the opacity of current_fs reduces to 0 - stored in "now"
                //1. scale previous_fs from 80% to 100%
                scale = 0.8 + (1 - now) * 0.2;
                //2. take current_fs to the right(50%) - from 0%
                left = ((1 - now) * 50) + "%";
                //3. increase opacity of previous_fs to 1 as it moves in
                opacity = 1 - now;
                current_fs.css({
                    'left': left
                });
                previous_fs.css({
                    'transform': 'scale(' + scale + ')',
                    'opacity': opacity
                });
            },
            duration: 800,
            complete: function () {
                current_fs.hide();
                animating = false;
            },
            //this comes from the custom easing plugin
            //easing: 'easeInOutBack'
        });
    });

    $(".submit").click(function () {
        return false;
    })



    var $window = window,
        offset = '90%',
        $doc = $(document),
        self = this,
        $body = $('body'),
        TweenMax = window.TweenMax,
        TweenLite = window.TweenLite,
        fl_theme = window.fl_theme || {};
    fl_theme.window = $(window);
    fl_theme.document = $(document);
    window.fl_theme = fl_theme;
    fl_theme.window = $(window);
    fl_theme.sameOrigin = true;

    // COMMENT RAITING
    fl_theme.initRatingCustom = function () {

        $("body").on("init", "#rating-autos", function () {
                $("#rating-autos").hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>')
            }).on("click", "#respond p.stars a", function () {
                var e = $(this),
                    t = $(this).closest("#respond").find("#rating-autos"),
                    i = $(this).closest(".stars");
                return t.val(e.text()),
                    e.siblings("a").removeClass("active"),
                    e.addClass("active"),
                    i.addClass("selected"),
                    !1
            }),
            $("#rating-autos").trigger("init");

        $('.comments-list').on("DOMNodeInserted", function (event) {
            $(this).find('#rating-autos').removeAttr('required');
        });


    };

    fl_theme.initAdded = function () {
        setTimeout(function func() {
            jQuery('.tmreviews_added_notice').removeClass('tmreviews_added_notice_visible');
        }, 2500);
    };

    fl_theme.initSelectFunction = function () {
        $('.tmnice-select').niceSelect();
    };


    fl_theme.initAddDelProsCons = function () {
        $('.comment_pros_cons_add').on('click', function () {
            $(this).parents('.author_comment_pros_cons').append('<div class="author_comment_pros_cons_contain">' +
                '<input id="fl-pros" name="tmreviews_review_pros[]" type="text" class="fl-pros" placeholder="Pros if have">' +
                '<input id="fl-cons" name="tmreviews_review_cons[]" type="text" class="fl-cons" placeholder="Cons if have">' +
                '<span class="comment_pros_cons_del"></span>' +
                '</div>');
        });

        $('.author_comment_pros_cons').on('click', '.comment_pros_cons_del', function (event) {
            $(this).parent('.author_comment_pros_cons_contain').remove();
            $(this).remove();
        });

    };

    fl_theme.initAddDelEmployers = function () {
        $('.employers_add').on('click', function () {
            $(this).parents('.fl-employer').append('<div class="fl-employer-container">' +
                ' <div class="tmreviews-col">' +
                    '<div class="tmreviews-form-field-icon"></div>' +
                    '<div class="tmreviews-field-lable">' +
                    '<label>Avatar Image</label>' +
                    '</div>' +
                    '<div class="tmreviews-field-input tmreviews_fileinput">' +
                    '<div class="tmreviews_repeat">' +
                    '<input title="" type="file" class="tmreviews_file " name="empl_img[]" data-filter-placeholder="" />' +
                    '<div class="errortext" style="display:none;"></div>' +
                    '</div>' +
                    '</div>' +
                '</div>' +
                '<input id="fl-pros" name="empl_name[]" type="text" class="fl-empl" placeholder="Employer Name">' +
                '<input id="fl-cons" name="empl_pos[]" type="text" class="fl-empl" placeholder="Employer Position">' +
                '<span class="empl_del"></span>' +
                '</div>');
        });

        $('.fl-employer').on('click', '.empl_del', function (event) {
            $(this).parent('.fl-employer-container').remove();
            $(this).remove();
        });

    };


    fl_theme.initCustomFunction = function () {
        fl_theme.initRatingCustom();
        fl_theme.initAdded();
        fl_theme.initAddDelProsCons();
        fl_theme.initAddDelEmployers();
    };



    fl_theme.initCustomFunction();
    fl_theme.initSelectFunction();


    if ($('.fl-user-reviews-content .fl-user-reviews-contain').length === 0) {
        $('.fl-user-reviews-content').html('<span class="tmvendors_no_places">You didn\'t add any reviews</span>');
    }
});
