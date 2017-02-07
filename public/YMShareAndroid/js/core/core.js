/**
 * Created by qcj on 15/10/20.
 */
var g_jq_dom = {
    $doc:$(document),
    $wnd:$(window),
    $html:$('html'),
    $body:$('body')
};

var g_var = {
    core_version:'1.0.17',
    wnd_width:g_jq_dom.$wnd.width(),
    wnd_height:g_jq_dom.$wnd.height(),
    page_width:g_jq_dom.$wnd.width(),
    max_width:750,
    scroll_bar_width:null,
    current_page_scroll_top:0
};

if (g_var.page_width > g_var.max_width){
    g_var.page_width = g_var.max_width;
}
g_var.scale_ratio = g_var.page_width / 750;

var g_event = {
    css_ani_event:"webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
    touchend:"touchend",
    touchmove:"touchmove mousewheel",
    touchstart:"touchstart mousedown",
    keypress:"keypress",
    keydown:"keydown",
    keyup:"keyup",
    focus:"focus",
    blur:"blur"
};

function page_overflow_dash_layout($el){
    $el = $el || g_jq_dom.$body;
    var $all_cf_dash_el = $el.find('[data-cf-dash]');

    $all_cf_dash_el.each(function(){
        var $this = $(this);

        var maxFontCnt = $this.attr("data-cf-dash");
        var countText = $this.text();
        countText = countText.trim();
        if('' != countText){
            if(maxFontCnt < countText.length){
                var i = 0, final_str_len= 0, str_width_count = 0;
                for(i=0; i<countText.length; i++){
                    var this_char = countText.charAt(i);
                    if((this_char > 'a' && this_char < 'z')
                        || ' ' == this_char){
                        str_width_count += 0.5;
                    } else {
                        str_width_count += 1;
                    }
                    final_str_len++;
                    if((maxFontCnt - 1) < Math.ceil(str_width_count)){
                        break;
                    }
                }
                var ort_text_len = countText.length;
                countText = countText.substr(0, final_str_len);
                if(final_str_len != ort_text_len){
                    countText += '...';
                }
                $this.text(countText);
            }
        }
    });
}
function page_layout($el, undefined){

    $el = $el || g_jq_dom.$body;
    var $all_cf_layout_el = $el.find('[data-cf-layout]');

    $all_cf_layout_el.each(function(){
        var $this = $(this);
        var layout_cfg = $this.attr('data-cf-layout');

        layout_cfg = JSON.parse(layout_cfg);

        if(layout_cfg.minHeight){
            layout_cfg.minHeight = Math.floor(layout_cfg.minHeight * g_var.scale_ratio);
            if(0 == layout_cfg.minHeight){layout_cfg.minHeight = 1;}
        }

        if(layout_cfg.minWidth){
            layout_cfg.minWidth = Math.floor(layout_cfg.minWidth * g_var.scale_ratio);
            if(0 == layout_cfg.minWidth){layout_cfg.minWidth = 1;}
        }

        if(layout_cfg.width){
            layout_cfg.width = Math.floor(layout_cfg.width * g_var.scale_ratio);
            if(0 == layout_cfg.width){layout_cfg.width = 1;}
        }
        if(layout_cfg.height){
            layout_cfg.height = Math.floor(layout_cfg.height * g_var.scale_ratio);
            if(0 == layout_cfg.height){layout_cfg.height = 1;}
        }
        if(layout_cfg.roundBorder){
            layout_cfg.width = layout_cfg.width + layout_cfg.width%2;
            layout_cfg.height = layout_cfg.height + layout_cfg.height%2;
            layout_cfg.roundBorder = undefined;
        }

        if(layout_cfg.fontSize){
            layout_cfg.fontSize = Math.floor(layout_cfg.fontSize * g_var.scale_ratio)+"px";
        }

        if(layout_cfg.verticalCenter){
            if(!layout_cfg.lineHeight){
                layout_cfg.lineHeight = Math.floor(layout_cfg.height)+'px';
            } else {
                layout_cfg.lineHeight = Math.floor(layout_cfg.lineHeight*g_var.scale_ratio)+'px';
            }

            layout_cfg.verticalCenter = undefined;
        } else if(layout_cfg.lineHeight){
            layout_cfg.lineHeight = Math.floor(layout_cfg.lineHeight*g_var.scale_ratio)+'px';
        }

        if(layout_cfg.maxFontCnt){
            $this.attr('data-cf-dash', layout_cfg.maxFontCnt);
            layout_cfg.maxFontCnt = undefined;
        }
        if(layout_cfg.letterSpacing){
            layout_cfg.letterSpacing = layout_cfg.letterSpacing * g_var.scale_ratio;
        }

        if(layout_cfg.paddingLeft){
            layout_cfg.paddingLeft = layout_cfg.paddingLeft * g_var.scale_ratio;
        }

        if(layout_cfg.paddingRight){
            layout_cfg.paddingRight = layout_cfg.paddingRight * g_var.scale_ratio;
        }

        if(layout_cfg.paddingTop){
            layout_cfg.paddingTop = layout_cfg.paddingTop * g_var.scale_ratio;
        }

        if(layout_cfg.paddingBottom){
            layout_cfg.paddingBottom = layout_cfg.paddingBottom * g_var.scale_ratio;
        }

        if(layout_cfg.marginLeft){
            layout_cfg.marginLeft = layout_cfg.marginLeft * g_var.scale_ratio;
        }

        if(layout_cfg.marginRight){
            layout_cfg.marginRight = layout_cfg.marginRight * g_var.scale_ratio;
        }

        if(layout_cfg.marginTop){
            layout_cfg.marginTop = layout_cfg.marginTop * g_var.scale_ratio;
        }

        if(layout_cfg.marginBottom){
            layout_cfg.marginBottom = layout_cfg.marginBottom * g_var.scale_ratio;
        }

        if(layout_cfg.left){
            layout_cfg.left = layout_cfg.left * g_var.scale_ratio;
        }

        if(layout_cfg.top){
            layout_cfg.top = layout_cfg.top * g_var.scale_ratio;
        }

        if(layout_cfg.right){
            layout_cfg.right = layout_cfg.right * g_var.scale_ratio;
        }
        if(layout_cfg.bottom){
            layout_cfg.bottom = layout_cfg.bottom * g_var.scale_ratio;
        }
        if(layout_cfg.border){
            layout_cfg.border.width = Math.floor(layout_cfg.border.width * g_var.scale_ratio);
            layout_cfg.border = layout_cfg.border.width+"px "+layout_cfg.border.style+" "+layout_cfg.border.color;
        }
        $this.css(layout_cfg).removeAttr('data-cf-layout');
    });
}

function resize_body_to_wnd(){
    g_jq_dom.$body.css({"min-height":g_jq_dom.$wnd.height()});
}
function set_touch_event(){
    var is_support_touch = ("ontouchend" in document);
    if(is_support_touch){
        g_event.touchend = 'touchend';
    }
}

$(function(){
    set_touch_event();
    page_layout();
    page_overflow_dash_layout();
    resize_body_to_wnd();
    g_jq_dom.$body.removeClass('cf-invisible');
});
