(function ($) {
   var offset = '90%';
    "use strict";
    !(function (t) {
        var e = {};
        function o(i) {
            if (e[i]) return e[i].exports;
            var n = (e[i] = { i: i, l: !1, exports: {} });
            return t[i].call(n.exports, n, n.exports, o), (n.l = !0), n.exports;
        }
        (o.m = t),
            (o.c = e),
            (o.d = function (t, e, i) {
                o.o(t, e) || Object.defineProperty(t, e, { enumerable: !0, get: i });
            }),
            (o.r = function (t) {
                "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(t, "__esModule", { value: !0 });
            }),
            (o.t = function (t, e) {
                if ((1 & e && (t = o(t)), 8 & e)) return t;
                if (4 & e && "object" == typeof t && t && t.__esModule) return t;
                var i = Object.create(null);
                if ((o.r(i), Object.defineProperty(i, "default", { enumerable: !0, value: t }), 2 & e && "string" != typeof t))
                    for (var n in t)
                        o.d(
                            i,
                            n,
                            function (e) {
                                return t[e];
                            }.bind(null, n)
                        );
                return i;
            }),
            (o.n = function (t) {
                var e =
                    t && t.__esModule
                        ? function () {
                            return t.default;
                        }
                        : function () {
                            return t;
                        };
                return o.d(e, "a", e), e;
            }),
            (o.o = function (t, e) {
                return Object.prototype.hasOwnProperty.call(t, e);
            }),
            (o.p = ""),
            o((o.s = 2));
    })({
        2: function (t, e) {
            var o = function (t, e) {
                var o = t,
                    i = o.data("id"),
                    n = elementorFrontend.isEditMode(),
                    a = {},
                    k = {};
                if (n) {

                    var r,
                        l = {};
                    if (!window.elementor.hasOwnProperty("elements")) return !1;
                    if (!(r = window.elementor.elements).models) return !1;
                    e.each(r.models, function (t, r) {

                        r.id == o.closest(".elementor-top-section").data("id") &&
                        e.each(r.attributes.elements.models, function (t, r) {
                            e.each(r.attributes.elements.models, function (t, r) {
                                e.each(r.attributes.elements.models, function (t, r) {
                                    e.each(r.attributes.elements.models, function (t, e) {
                                        return i == e.id &&
                                        ((l = e.attributes.settings.attributes),

                                            (a.tm_reviews_custom_animation = l.tm_reviews_custom_animation),
                                            (k.tm_reviews_animation_delay = l.tm_reviews_animation_delay),
                                            "disable" != a.tm_reviews_custom_animation ? (o.removeClass("animation-complete").removeClass("fl-animated-item-velocity").addClass("fl-animated-item-velocity") ,o.attr("data-animate-type", a.tm_reviews_custom_animation ), s()) : o.removeAttr('data-animate-type'),o.removeClass("animation-complete").removeClass("fl-animated-item-velocity"),o.removeAttr('data-item-delay'),
                                            ""!=k.tm_reviews_animation_delay ? o.attr("data-item-delay", k.tm_reviews_animation_delay , s()): o.removeAttr('data-item-delay'),
                                        0 !== a.length)
                                            ? a
                                            : !(!n || !a) && void 0;
                                    });
                                });
                            });
                        }),
                            e.each(r.attributes.elements.models, function (t, r) {
                                e.each(r.attributes.elements.models, function (t, e) {
                                    return i == e.id &&
                                    ((l = e.attributes.settings.attributes),
                                        (a.tm_reviews_custom_animation = l.tm_reviews_custom_animation),
                                        (k.tm_reviews_animation_delay = l.tm_reviews_animation_delay),
                                        "disable" !=  a.tm_reviews_custom_animation ? (console.log(e.attributes.settings.attributes),o.removeClass("animation-complete").removeClass("fl-animated-item-velocity").addClass("fl-animated-item-velocity"),o.attr("data-animate-type", a.tm_reviews_custom_animation ), s()) : o.removeAttr('data-animate-type'),o.removeClass("animation-complete").removeClass("fl-animated-item-velocity"),o.removeAttr('data-item-delay'),
                                        ""!=k.tm_reviews_animation_delay ? o.attr("data-item-delay", k.tm_reviews_animation_delay , s()): o.removeAttr('data-item-delay'),
                                    0 !== a.length)
                                        ? a
                                        : !(!n || !a) && void 0;
                                });
                            });
                        function s() {
                           // o.attr("id", "eael-section-tooltip-" + i);
                           // var t = "." + o.attr("class");
                            //fl_theme.initVelocityAnimationSave();
                            var animated_velocity = $('.fl-animated-item-velocity');

                            // animated Function
                            animated_velocity.each(function () {
                                var $this_item = $(this), $item, $animation;
                                // Hided item if animated not complete
                                animated_velocity.each(function () {
                                    var $this = $(this),
                                        $item;

                                    if ($this.data('item-for-animated')) {
                                        $item = $this.find($this.data('item-for-animated'));
                                        $item.each(function() {
                                            if(!$(this).hasClass('animation-complete')) {
                                                $(this).css('opacity','0');
                                            }
                                        });
                                    } else {
                                        if(!$this.hasClass('animation-complete')) {
                                            $this.css('opacity','0');
                                        }
                                    }
                                });


                                if ($this_item.data('item-for-animated')) {
                                    $item = $this_item.find($this_item.data('item-for-animated'));
                                    $item.each(function() {
                                        var $this = $(this);
                                        $this.waypoint(function () {
                                                if(!$this.hasClass('animation-complete')) {
                                                    $this.addClass('animation-complete')
                                                        .velocity('transition.'+a.tm_reviews_custom_animation,{delay:k.tm_reviews_animation_delay,display:'undefined',opacity:1});
                                                }
                                            },
                                            {
                                                offset: offset
                                            });
                                    });
                                } else {
                                    $this_item.waypoint(function () {
                                            if(!$this_item.hasClass('animation-complete')) {
                                                $this_item.addClass('animation-complete')
                                                    .velocity('transition.'+a.tm_reviews_custom_animation,{  delay:k.tm_reviews_animation_delay,display:'undefined',opacity:1});
                                            }

                                        },
                                        {
                                            offset: offset
                                        });
                                }

                        })
                        }
                    });
                }
            };
            jQuery(window).on("elementor/frontend/init", function () {
                elementorFrontend.hooks.addAction("frontend/element_ready/widget", o);
            });
        },
    });
})(jQuery);