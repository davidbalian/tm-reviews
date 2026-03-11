jQuery.noConflict()(function($) {

    "use strict";
    
    
    
    
    $(".no-vrf-reply").click(function(){
  $(".alert.alert-warning").show(1000);
 });
 
    
    
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
    fl_theme.initRatingCustom = function() {

        $("body").on("init", "#rating-autos", function() {
            $("#rating-autos").hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>')
        }).on("click", "#respond p.stars a", function() {
            var e = $(this)
                , t = $(this).closest("#respond").find("#rating-autos")
                , i = $(this).closest(".stars");
            return t.val(e.text()),
                e.siblings("a").removeClass("active"),
                e.addClass("active"),
                i.addClass("selected"),
                !1
        }),
            $("#rating-autos").trigger("init");

    };

    fl_theme.initAdded = function(){
        setTimeout(function func() {
            jQuery('.tmreviews_added_notice').removeClass('tmreviews_added_notice_visible');
        }, 2500);
    };



    fl_theme.initCustomFunction = function(){
        fl_theme.initRatingCustom();
       // fl_theme.initAddDelProsCons();
    };
    
     


    fl_theme.initCustomFunction();



});