/**
 * Created by JMCX - WHY
 * Date: 2015/7/29
 */
(function(win, $, undefined){
    var _camera_plus = function(){};
    var $_input, $_show_panel, $_small_img;
    var _out_width, _out_height, _file_reader=new FileReader(), _mp_img, _face_orientation;
    var _callback;

    var start_camera = function(options){
        _out_width = options.width || 512;
        _out_height = options.height || 512;
        $_show_panel = options.show_panel || undefined;
        $_small_img = options.small_img || undefined;
        _callback = options.callback;
        $_input.click();
    };

    _camera_plus.prototype = {
        start_camera:start_camera
    };

    var camera_plus = win.camera_plus = new _camera_plus();

    function _get_image(){
        if (typeof FileReader === 'undefined') {
            alert ('Your browser does not support FileReader...');
            return;
        }

        var file = this.files[0];
        if(!file) return;

        _mp_img = new MegaPixImage(file);

        function _get_img_orientation(exif_info){
            if(exif_info){
                if(exif_info.Orientation){
                    _face_orientation = exif_info.Orientation;
                }
            }
        }
        $_input.fileExif(_get_img_orientation);

        _file_reader.onload = function(e) {
            if($_small_img){
                _mp_img.render(
                    $_small_img.get(0),
                    { maxWidth: 128, maxHeight: 128, orientation : _face_orientation}
                );
            }
            if($_show_panel){
                _mp_img.render(
                    $_show_panel.get(0),
                    { maxWidth: _out_width, maxHeight: _out_height, orientation : _face_orientation}
                );
            }
            $_show_panel.on("load._camera_plus", function(){
                $_show_panel.off("load._camera_plus");
                if($.isFunction(_callback)){
                    _callback();
                }
                _callback = undefined;
                re_init();
            });
        };
        _file_reader.readAsDataURL(file);
    }

    function re_init(){
        $_input = undefined;
        $_input = $('<input type="file" accept="image/*" style="display:none">');
        $_input.on("change.camera_plus", _get_image);
    }
    re_init();
})(window, jQuery);

(function(win, $, undefined){
    var _img_plus = function(){};
    var _out_width, _out_height, _start_x, _start_y, _wnd_width, _wnd_height;

    var scale_img = function($img, step){
        $img.css({
            width:$img.width()+step
        });
        return this;
    };
    var cut_img = function($img, options, $out_img){
        var output = document.createElement("canvas");
        var temp_img = document.createElement("img");
        $img.css({
            width:$img.width()+1
        });

        temp_img.src = $img.attr("src");

        var cur_width = $img.width()-1;
        var cur_height = $img.height();
        var org_width = options.org_width || temp_img.width;
        var org_height = options.org_height || temp_img.height;

        _out_width = options.output_width || cur_width;
        _out_height = options.output_height || cur_height;
        _start_x = options.start_x || 0;
        _start_y = options.start_y || 0;

        //--坐标换算。
        var len_scale_x = _out_width / cur_width;
        var len_scale_y = _out_height / cur_height;

        var cut_scale_x = Math.abs(_start_x) / cur_width;
        var cut_scale_y =  Math.abs(_start_y) / cur_height;

        var _o_x=org_width * cut_scale_x;
        var _o_y=org_height * cut_scale_y;

        //--长度换算
        var _o_width=Math.floor(org_width * len_scale_x);
        var _o_height=Math.floor(org_height * len_scale_y);

        output.width = _out_width;
        output.height = _out_height;

        //获取二维画布上下文
        var ctx = output.getContext("2d");

        //将画布填充为白色
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0,0,output.width, output.height);
        ctx.drawImage($img.get(0), _o_x, _o_y, _o_width, _o_height, 0, 0, output.width, output.height);

        //将图像数据，转化为可以通过url传递的数据
        if($out_img){
            $out_img.attr("src", output.toDataURL("image/jpg"));
        }

        return output.toDataURL("image/jpg");
    };

    _img_plus.prototype = {
        scale_img:scale_img,
        cut_img:cut_img
    };

    var img_plus = win.img_plus = new _img_plus();
})(window, jQuery);