<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
  
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  /*
   * 注意：
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 常见问题及完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈：
   * 邮箱地址：weixin-open@qq.com
   * 邮件主题：【微信JS-SDK反馈】具体问题
   * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','previewImage','chooseImage','uploadImage','downloadImage']
  });
  wx.ready(function () {




        //var imgUrl = 'https://www.baidu.com/img/baidu_jgylogo3.gif';

    var title = '微信JSSDK分享的标题';

    var desc = "通过微信分享的描述";

    var link = 'http://www.egret.com/';

    var imgUrl = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png";

    wx.ready(function(){

    //朋友圈

    wx.onMenuShareTimeline({

        title: title, desc: desc, link: link, imgUrl: imgUrl,

        success: function () {

           //alert('Share friends OK');



			//朋友圈首页分享成功，记录分享记录

			var url = window.location.href;

			$.post(

			'<?php echo $site_url; ?>ajax/share',

			{

				act:"<?php echo isset($item['id']) ? $item['id'] : '';?>",

				actType:'item',

				scene:'THBT_002',

				shareTo:'wx_timeline',

				sourceUrl:url,

				targetUrl:link

			}

			);



        },cancel: function () {

           //alert('Share friends Canceled');

        }

    });

    //朋友

    wx.onMenuShareAppMessage({

        title: title, desc: desc,  link: link,  imgUrl: imgUrl,  dataUrl: '',type: 'link', 

        success: function () {

           //alert('Share friends OK');



			//朋友首页分享成功，记录分享记录

			var url = window.location.href;

			$.post(

			'<?php echo $site_url; ?>ajax/share',

			{

				act:"<?php echo isset($item['id']) ? $item['id'] : '';?>",

				actType:'item',

				scene:'THBT_002',

				shareTo:'wx_appmsg',

				sourceUrl:url,

				targetUrl:link

			}

			);



        },cancel: function () {

           //alert('Share friends Canceled');

        }

    });  

    //wx.hideOptionMenu();

  }); 


  });
</script>
</html>
