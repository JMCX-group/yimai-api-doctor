<?php
/**
 * Created by JMCX - WHY
 * Date: 2016/1/30
 */
if(!isset($_GET['username'])){
    exit(0);
}

if(!isset($_GET['usercode'])){
    exit(0);
}

if(!isset($_GET['userid'])){
    exit(0);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <title>医者脉连</title>
    <?php require_once(dirname(__FILE__).'/page_parts/common/base_url.php');?>
<!--    --><?php //require_once(dirname(__FILE__).'/page_parts/common/css.php');?>
    <link rel="stylesheet" href="css/core/core.css?v=1.0.17">

    <style>
        #pag-name {
            position: absolute;
            color: #33a8dd;
        }

        #page-download {
            position: absolute;
        }

        #page-qr {
            position: absolute;
            background-color: #c22;
        }

        #pag-code {
            position: absolute;
            color: #33a8dd;
        }
    </style>

</head>
<body class="cf-invisible">

<div class="cf-wrap cf-wrap-no-bottom" data-cf-layout='{"height": 3196}'>
    <img src="img/YMShareBkg.jpg" class="cf-img-bkg">
    <div class="cf-row" id="pag-name"
         data-cf-layout='{
         "top": 660,
         "left": 50,
         "fontSize": 36,
         "position": "absolute"}'>我是<?php echo $_GET['username']?>医生</div>
    <a href="https://itunes.apple.com/cn/app/yi-zhe-mai-lian/id1017581565" id="page-download" class="cf-row"
         data-cf-layout='{
         "top": 1354,
         "left": 50,
         "width": 300,
         "height": 82
         }'
    ></a>

    <img id="page-qr" src="http://d.medi-link.cn/qrcode/<?php echo $_GET["userid"] ?>.png" class="cf-row"
         data-cf-layout='{"width": 200, "height": 200, "top": 1762, "left": 60}'>

    <div id="pag-code" class="cf-row"
         data-cf-layout='{
         "top": 2220,
         "left": 60,
         "fontSize": 38
         }'><?php echo $_GET['usercode'] ?></div>

</div>

<?php require_once(dirname(__FILE__).'/page_parts/common/js.php');?>
<script src="js/lib/jquery.slides.min.js"></script>
<script src="js/lib/jquery.exif.js"></script>
<script src="js/lib/MegaPixImage.js"></script>
<script src="js/lib/common.js"></script>
<script src="js/lib/AlloyImage/alloyimage.js"></script>
<!--1.0.18-->
<script src="js/page/index.js?v=0.0.3"></script>

<script>

    $(function(){
        console.log("got here");
        g_jq_dom.$body.removeClass("cf-invisible");
        $("#page-download").on(g_event.touchend, function () {
            if(is_weixn()) {
                alert("请点击右上角，在系统浏览器中打开此链接。")
            }
        });
    });

    function is_weixn(){
        var ua = navigator.userAgent.toLowerCase();
        return (ua.match(/MicroMessenger/i) == "micromessenger")
    }
</script>
</body>
</html>
