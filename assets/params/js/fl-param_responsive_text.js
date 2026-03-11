// JavaScript Document
$tmreviews_helping_responsive = jQuery.noConflict();

$tmreviews_helping_responsive(".fl-helping-responsive-wrapper").each(function(index, element) {
    var t = $tmreviews_helping_responsive(element);
    get_responsive_values_in_input(t);
    set_responsive_values_in_hidden(t);
});

/*
 *   Get hidden field values
 *---------------------------------------------------*/
function get_responsive_values_in_input(t) {
    var mv = t.find(".fl-helping-responsive-value").val();

    /* TOGGLE */
    var toggleMedia = new Object();

    if (mv != "") {
        var vals = mv.split(";");
        $tmreviews_helping_responsive.each(vals, function(i, vl) {
            if (vl != "") {
                t.find(".fl-responsive-input").each(function(input_index, elem) {
                    var splitval = vl.split(":");
                    var dataid = $tmreviews_helping_responsive(elem).attr("data-id");

                    if( dataid==splitval[0] ) {
                        var unit = $tmreviews_helping_responsive(elem).attr("data-unit");
                        mval = splitval[1].split(unit);
                        $tmreviews_helping_responsive(elem).val(mval[0]);

                        /* TOGGLE */
                        toggleMedia[dataid] = mval[0];
                    }
                });
            }
        });

        /* TOGGLE */
        Object.size = function(obj) {
            var size = 0, key;
            for (key in obj) {
                if (obj.hasOwnProperty(key)) size++;
            }
            return size;
        };
    } else {
        var i=0;      // set default - Values
        t.find(".fl-responsive-input").each(function(input_index, elem) {
            var d = $tmreviews_helping_responsive(elem).attr("data-default");
            if(d!='') { $tmreviews_helping_responsive(elem).val(d); i=i+1; }
        });
    }
}


/*
 *   Set hidden field values
 *---------------------------------------------------*/
//  On change - input / select
$tmreviews_helping_responsive(".fl-responsive-input").on('change', function(e){
    var t = $tmreviews_helping_responsive(this).closest('.fl-helping-responsive-wrapper');
    //alert(t.attr("id"));
    set_responsive_values_in_hidden(t);
});

function set_responsive_values_in_hidden(t) {
    var nval = "";

    //  add all spacing widths, margins, paddings
    t.find(".fl-responsive-input").each(function(index, elm) {
        var unit = $tmreviews_helping_responsive(elm).attr("data-unit");

        var ival = $tmreviews_helping_responsive(elm).val();
        if ($tmreviews_helping_responsive.isNumeric(ival)) {
            if (ival.match(/^[0-9]+$/))
                var item = $tmreviews_helping_responsive(elm).attr("data-id") + ":" + $tmreviews_helping_responsive(elm).val() + unit + ";";
            nval += item;
        }
    });
    t.find(".fl-helping-responsive-value").val(nval);
}