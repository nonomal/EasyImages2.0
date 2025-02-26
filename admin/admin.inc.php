<?php
/*
 * 简单图床设置页面
 * 2022-1-24 05:57:35
 */

require_once __DIR__  . '/../application/header.php';
require_once APP_ROOT . '/config/api_key.php';
require_once  APP_ROOT  . '/config/config.guest.php';

// 检查登录
if (!is_who_login('admin')) {
    echo '
  <script> new $.zui.Messager("请登录后再修改! ", {
	  type: "danger", // 定义颜色主题 
	  icon: "exclamation-sign" // 定义消息图标
  }).show();</script>';
    header("refresh:2;url=" . $config['domain'] . "/admin/index.php");
    require_once APP_ROOT . '/application/footer.php';
    exit;
}

// 修改config配置
if (isset($_POST['update'])) {
    $postArr = $_POST;
    $new_config = array_replace($config, $postArr);
    $config_file = APP_ROOT . '/config/config.php';
    cache_write($config_file, $new_config);
    echo '
  <script>
  new $.zui.Messager("保存成功", {
    type: "primary", // 定义颜色主题 
    icon: "ok-sign" // 定义消息图标
  }).show();
  </script>  
  ';
    header("refresh:1;");
}

// 添加token
if (isset($_POST['add_token_id'])) {
    // $_POST['add_token'] 生成的Token
    // $_POST['add_token_id'] Token的ID
    //  $_POST['add_token_expired'] 过期时间
    $postArr = array(
        $_POST['add_token'] => array(
            'id' => $_POST['add_token_id'], 'expired' => $_POST['add_token_expired'] * 86400 + time(), 'add_time' => time()
        )
    );
    $new_config = array_replace($tokenList, $postArr);
    $config_file = APP_ROOT . '/config/api_key.php';
    cache_write($config_file, $new_config, 'tokenList');
    echo '
  <script>
  new $.zui.Messager("上传用户添加成功!", {
    type: "primary", // 定义颜色主题 
    icon: "ok-sign" // 定义消息图标
  }).show();
  </script>  
  ';
    header("refresh:1;");
}
// 禁用Token 
if (isset($_GET['stop_token'])) {

    // unset($tokenList[$_GET['delete_token']]);
    $stop_token =  $_GET['stop_token'];
    $postArr = array(
        $stop_token => array(
            'id' => 0, 'expired' => time()
        )
    );
    $new_config = array_replace($tokenList, $postArr);
    $config_file = APP_ROOT . '/config/api_key.php';
    cache_write($config_file, $new_config, 'tokenList');
    echo '
        <script>
        new $.zui.Messager("禁用Token成功!", {
            type: "primary", // 定义颜色主题 
            icon: "ok-sign" // 定义消息图标
        }).show();
        </script>  
  ';
    header("refresh:2;url=" . $config['domain'] . "/admin/admin.inc.php");
}

// 删除guset.config.php数组对
if (isset($_GET['delete_guest'])) {
    unset($guestConfig[$_GET['delete_guest']]);
    $config_file = APP_ROOT . '/config/config.guest.php';
    cache_write($config_file, $guestConfig, 'guestConfig');
    echo '
  <script>
  new $.zui.Messager("删除上传用户成功!", {
    type: "primary", // 定义颜色主题 
    icon: "ok-sign" // 定义消息图标
  }).show();
  </script>  
  ';
}

// 添加上传账号 修改config.guest.php
if (isset($_POST['uploader_form'])) {
    $postArr = array($_POST['uploader_user'] => $_POST['uploader_password']);
    $new_config = array_replace($guestConfig, $postArr);
    $config_file = APP_ROOT . '/config/config.guest.php';
    cache_write($config_file, $new_config, 'guestConfig');
    echo '
  <script>
  new $.zui.Messager("上传用户添加成功!", {
    type: "primary", // 定义颜色主题 
    icon: "ok-sign" // 定义消息图标
  }).show();
  </script>  
  ';
    header("refresh:1;");
}
// 删除非空目录
if (isset($_POST['delDir'])) {
    $delDir = APP_ROOT . $config['path'] . $_POST['delDir'];
    if (deldir($delDir)) {
        echo '
		<script> new $.zui.Messager("删除成功! ", {
			type: "success", // 定义颜色主题 
			icon: "ok-sign" // 定义消息图标
		}).show();</script>';
        header("refresh:1;"); // 1s后刷新当前页面
    } else {
        echo '
		<script> new $.zui.Messager("删除失败! ", {
			type: "danger", // 定义颜色主题 
			icon: "exclamation-sign" // 定义消息图标
		}).show();</script>';
        header("refresh:1;"); // 1s后刷新当前页面
    }
}

// 监黄恢复图片
if (isset($_GET['suspic_reimg'])) {
    $name = $_GET['suspic_reimg'];
    if (re_checkImg($name)) {
        echo "
        <script>
        new $.zui.Messager('恢复成功', {
            type: 'success', // 定义颜色主题
            icon: 'ok'
        }).show();
        </script>
        ";
    } else {
        echo "
        <script>
        new $.zui.Messager('文件不存在!', {
            type: 'danger', // 定义颜色主题
            icon: 'warning-sign'
        }).show();
        </script>
        ";
    }
}
// 回收站恢复图片
if (isset($_GET['recycle_reimg'])) {
    $name = $_GET['recycle_reimg'];
    if (re_checkImg($name, 'recycle/')) {
        echo "
        <script>
        new $.zui.Messager('恢复成功', {
            type: 'success', // 定义颜色主题
            icon: 'ok'
        }).show();
        </script>
        ";
    } else {
        echo "
        <script>
        new $.zui.Messager('文件不存在!', {
            type: 'danger', // 定义颜色主题
            icon: 'warning-sign'
        }).show();
        </script>
        ";
    }
}
?>
<div class="row" style="margin-bottom:100px">
    <div class="alert alert-primary alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
        <h5>目录保存以 年/月/日/ 递进,非必要请勿修改! 否则会导致部分操作不可用;</h5>
        <h5>本人仅为程序开源创作,如非法网站使用与本人无关,请勿用于非法用途;</h5>
        <h5>请为本人博客<a class="alert-link" href="https://blog.png.cm/" target="_blank">blog.png.cm</a>加上网址链接,谢谢支持。作为开发者你可以对相应的后台功能进行扩展(增删改相应代码),但请保留代码中相关来源信息(例如: <a class="alert-link" href="https://blog.png.cm/">本人博客</a>,邮箱等)。</h5>
        <a href="https://png.cm/" target="_blank"><button type="button" class="btn btn-mini"><i class="icon icon-external-link"></i> 演示网站</button></a>
        <a href="https://www.kancloud.cn/easyimage/easyimage" target="_blank"><button type="button" class="btn btn-mini"><i class="icon icon-hand-right"></i> 使用手册</button></a>
        <!-- <a href="https://support.qq.com/products/367633" target="_blank"><button type="button" class="btn btn-mini hidden-xs inline-block"><i class="icon icon-bug"></i> 问题反馈</button></a> -->
        <a href="https://qm.qq.com/cgi-bin/qm/qr?k=kpzasY8VdPfrJrpAYdk0rxQJjqddgyAm&jump_from=webapi" target="_blank"><button type="button" class="btn btn-mini"><i class="icon icon-qq"></i> 问题反馈</button></a>
        <a href="../public/images/wechat.jpg" title="您的赞美是我开发的动力!" data-toggle="lightbox" class="btn btn-mini" style="color:#329d38;"><i class="icon icon-wechat"></i> 打赏作者</a>
        <a href="../public/images/alipay.jpg" title="您的赞美是我开发的动力!" data-toggle="lightbox" class="btn btn-mini hidden-xs inline-block" style="color:#1970fc;"><i class="icon icon-zhifubao"></i> 打赏作者</a>
    </div>
    <div class="col-md-2 col-xs-4">
        <ul class="nav nav-tabs nav-stacked">
            <li><a data-tab href="#Content1">网站设置</a></li>
            <li><a data-tab href="#Content9">界面设置</a></li>
            <li><a data-tab href="#Content2">上传设置</a></li>
            <li><a data-tab href="#Content5">API 设置</a></li>
            <li><a data-tab href="#Content11">图片回收<span class="label label-badge label-success"><?php echo get_file_by_glob(APP_ROOT . $config['path'] . 'recycle', 'number'); ?></span></a></li>
            <li><a data-tab href="#Content7">可疑图片<span class="label label-badge label-success"><?php echo get_file_by_glob(APP_ROOT . $config['path'] . 'suspic', 'number'); ?></span></a></li>
            <li><a data-tab href="#Content4">压缩图片</a></li>
            <li><a data-tab href="#Content3">广告设置</a></li>
            <li><a data-tab href="#Content6">图床安全</a></li>
            <li><a data-tab href="#Content10">账号密码</a></li>
            <li><a data-tab href="#Content8">系统信息</a></li>
        </ul>
    </div>
    <div class="tab-content col-md-10 col-xs-8">
        <div class="tab-pane fade" id="Content1">
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <label>网站域名 | 末尾不加'/'</label>
                    <input type="url" class="form-control" name="domain" required="required" value="<?php echo $config['domain']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="如果只有一个域名请与上边一致">图片域名 | 末尾不加'/' </label>
                    <input type="text" class="form-control" name="imgurl" required="required" value="<?php echo $config['imgurl']; ?>" placeholder="末尾不加/" onkeyup="this.value=this.value.replace(/\s/g,'')" title="网站域名与图片链接域名可以不同,比如A域名上传,可以返回B域名图片链接,A、B需绑定到同一空间下,如果不变的话,下边2个填写成一样的! ">
                </div>
                <div class="form-group">
                    <label>网站标题</label>
                    <input type="text" class="form-control" name="title" required="required" value="<?php echo $config['title']; ?>" onkeyup="this.value=this.value.trim()">
                </div>
                <div class="form-group">
                    <label>网站关键字</label>
                    <textarea class="form-control" rows="2" name="keywords" required="required" onkeyup="this.value=this.value.replace(/\s/g,'')"><?php echo $config['keywords']; ?></textarea>
                </div>
                <div class="form-group">
                    <label>网站描述</label>
                    <textarea class="form-control" rows="2" name="description" required="required" onkeyup="this.value=this.value.replace(/\s/g,'')"><?php echo $config['description']; ?></textarea>
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="不同公告以a标签分割">网站公告 | 支持html</label>
                    <textarea class="form-control" rows="3" name="tips"><?php echo $config['tips']; ?></textarea>
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="HTML / 统计代码 / JS / CSS">页首代码 | 需闭合标签</label>
                    <textarea class="form-control" rows="2" name="customize"><?php echo $config['customize']; ?></textarea>
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="HTML / 统计代码 / JS / CSS">页脚代码 | 需闭合标签</label>
                    <textarea class="form-control" rows="2" name="footer"><?php echo $config['footer']; ?></textarea>
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="服务条款 / 隐私政策 / DMCA">使用条款| 支持HTML</label>
                    <textarea class="form-control" rows="2" name="terms"><?php echo $config['terms']; ?></textarea>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="static_cdn" value="0">
                        <input type="checkbox" name="static_cdn" value="1" <?php if ($config['static_cdn']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">静态文件CDN | 末尾不加'/'</label>
                    </div>
                    <input type="url" class="form-control" name="static_cdn_url" value="<?php echo $config['static_cdn_url']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')" data-toggle="tooltip" title="jsdelivr可在后边添加版本号 例:@2.5.6">
                </div>
                <div class="form-group">
                    <div>
                        <label>缩略图生成方式</label>
                    </div>
                    <div class="radio-primary">
                        <input type="radio" name="thumbnail" value="0" <?php if ($config['thumbnail'] === 0) echo 'checked="checked"'; ?> id="thumbnail0"><label for="thumbnail0" data-toggle="tooltip" title="直接输出上传图片,会导致流量增加"> 关闭</label>
                    </div>
                    <div class="radio-primary">
                        <input type="radio" name="thumbnail" value="1" <?php if ($config['thumbnail'] === 1) echo 'checked="checked"'; ?> id="thumbnail1"><label for="thumbnail1" data-toggle="tooltip" title="利用TimThumb生成 | 优点: 带缓存周期 | 缺点: 无法被cdn缓存"> 访问时生成 | 推荐</label>
                    </div>
                    <div class="radio-primary">
                        <input type="radio" name="thumbnail" value="2" <?php if ($config['thumbnail'] === 2) echo 'checked="checked"'; ?> id="thumbnail2"><label for="thumbnail2" data-toggle="tooltip" title="优点: 缩略图直链 | 缺点: 每日首次访问广场需刷新一次,有缓存不失效"> 访问时生成 | 直链</label>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content2">
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <label data-toggle="tooltip" title="前后需加英文'/' 例: /i/">存储路径</label>
                    <input type="text" class="form-control" name="path" required="required" value="<?php echo $config['path']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')" title="可根据Apache/Nginx配置安全,参考: https://blog.png.cm/981.html 或 README.md">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="请以英文 , 分割 最后一个扩展名后边不要加 ,">允许上传的图片扩展名</label>
                    <input type="text" class="form-control" name="extensions" required="required" value="<?php echo $config['extensions']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <div class="form-group">
                    <label>图片命名方式</label>
                    <select class="chosen-select form-control" name="imgName">
                        <option value="default" <?php if ($config['imgName'] == 'default') echo 'selected'; ?>>默认 - 上传时间+4位随机数的36进制 >> vx77yu</option>
                        <option value="date" <?php if ($config['imgName'] == 'date') echo 'selected'; ?>>上传时间 >> 192704</option>
                        <option value="crc32" <?php if ($config['imgName'] == 'crc32') echo 'selected'; ?>>CRC32 >> 2495551279</option>
                        <option value="unix" <?php if ($config['imgName'] == 'unix') echo 'selected'; ?>>Unix时间 >> 1635074840</option>
                        <option value="uniqid" <?php if ($config['imgName'] == 'uniqid') echo 'selected'; ?>>微秒时间 >> 6175436c73418</option>
                        <option value="source" <?php if ($config['imgName'] == 'source') echo 'selected'; ?>>源文件名 >> 微信图片_20211228214754</option>
                        <option value="md5" <?php if ($config['imgName'] == 'md5') echo 'selected'; ?>>MD5 >> 3888aa69eb321a2b61fcc63520bf6c82</option>
                        <option value="sha1" <?php if ($config['imgName'] == 'sha1') echo 'selected'; ?>>SHA1 >> 654faac01499e0cb5fb0e9d78b21e234c63d842a</option>
                        <option value="guid" <?php if ($config['imgName'] == 'guid') echo 'selected'; ?>>全局唯一标识符 >> 6EDAD0CC-AB0C-4F61-BCCA-05FAD65BF0FA</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>将上传图片转换为指定格式<?php echo $config['imgConvert']; ?></label>
                    <select class="chosen-select form-control" name="imgConvert">
                        <option value="" <?php if (empty($config['imgConvert'])) echo 'selected'; ?>>不转换</option>
                        <option value="webp" <?php if ($config['imgConvert'] == 'webp') echo 'selected'; ?>>WEBP</option>
                        <option value="png" <?php if ($config['imgConvert'] == 'png') echo 'selected'; ?>>PNG</option>
                        <option value="jpeg" <?php if ($config['imgConvert'] == 'jpeg') echo 'selected'; ?>>JPG</option>
                        <option value="gif" <?php if ($config['imgConvert'] == 'gif') echo 'selected'; ?>>GIF</option>
                        <option value="bmp" <?php if ($config['imgConvert'] == 'bmp') echo 'selected'; ?>>BMP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>单次最多上传 | 当前: </label><label id="maxUploadFiles"><?php echo $config['maxUploadFiles']; ?></label><label>张</label>
                    <input type="range" class="form-control" name="maxUploadFiles" value="<?php echo $config['maxUploadFiles']; ?>" min="1" max="100" step="1" onchange="document.getElementById('maxUploadFiles').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>水印方式</label>
                    <select class="chosen-select form-control" name="watermark">
                        <option value="0" <?php if (!$config['watermark']) echo 'selected'; ?>>关闭水印</option>
                        <option value="1" <?php if ($config['watermark'] == 1) echo 'selected'; ?>>文字水印</option>
                        <option value="2" <?php if ($config['watermark'] == 2) echo 'selected'; ?>>图片水印</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>水印文字</label>
                    <input type="text" class="form-control" name="waterText" required="required" value="<?php echo $config['waterText']; ?>" onkeyup="this.value=this.value.trim()">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="格式RGBA 末尾为透明度0-127 0为不透明,仅支持文字水印">水印颜色</label>
                    <input type="text" name="textColor" class="form-control" value="" readonly data-jscolor="{preset:'myPreset'}">
                </div>
                <div class="form-group">
                    <label>水印大小 | 当前: </label><label id="textSize"><?php echo $config['textSize']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="textSize" value="<?php echo $config['textSize']; ?>" min="5" max="200" step="5" onchange="document.getElementById('textSize').innerHTML=value">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="水印中含有中文的,请选用符合GB/2312的字体">文字字体路径</label>
                    <input type="text" class="form-control" name="textFont" required="required" value="<?php echo $config['textFont']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="支持GIF,JPG,BMP,PNG和PNG alpha">图片水印路径</label>
                    <input type="text" class="form-control" name="waterImg" required="required" value="<?php echo $config['waterImg']; ?>" onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="不开启水印方式不生效">水印位置</label>
                    <select class="chosen-select form-control" name="waterPosition">
                        <option value="0" <?php if (!$config['waterPosition']) echo 'selected'; ?>>随机位置</option>
                        <option value="1" <?php if ($config['waterPosition'] == 1) echo 'selected'; ?>>顶部居左</option>
                        <option value="2" <?php if ($config['waterPosition'] == 2) echo 'selected'; ?>>顶部居中</option>
                        <option value="3" <?php if ($config['waterPosition'] == 3) echo 'selected'; ?>>顶部居右</option>
                        <option value="4" <?php if ($config['waterPosition'] == 4) echo 'selected'; ?>>左边居中</option>
                        <option value="5" <?php if ($config['waterPosition'] == 5) echo 'selected'; ?>>图片中心</option>
                        <option value="6" <?php if ($config['waterPosition'] == 6) echo 'selected'; ?>>右边居中</option>
                        <option value="7" <?php if ($config['waterPosition'] == 7) echo 'selected'; ?>>底部居左</option>
                        <option value="8" <?php if ($config['waterPosition'] == 8) echo 'selected'; ?>>底部居中</option>
                        <option value="9" <?php if ($config['waterPosition'] == 9) echo 'selected'; ?>>底部居右</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>最大上传宽度 | 当前: </label><label id="maxWidth"><?php echo $config['maxWidth']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="maxWidth" value="<?php echo $config['maxWidth']; ?>" min="1024" max="51200" step="1024" onchange="document.getElementById('maxWidth').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>最大上传高度 | 当前: </label><label id="maxHeight"><?php echo $config['maxHeight']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="maxHeight" value="<?php echo $config['maxHeight']; ?>" min="1024" max="51200" step="1024" onchange="document.getElementById('maxHeight').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>单文件最大上传(1-50MB) | 当前: </label><label id="maxSize"><?php echo $config['maxSize'] / 1024 / 1024; ?></label><label>MB</label>
                    <input type="range" class="form-control" name="maxSize" value="<?php echo $config['maxSize']; ?>" min="1048576" max="52428800" step="1048576" onchange="document.getElementById('maxSize').innerHTML=value/1024/1024">
                </div>
                <div class="form-group">
                    <label>最小上传宽度 | 当前: </label><label id="minWidth"><?php echo $config['minWidth']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="minWidth" value="<?php echo $config['minWidth']; ?>" min="5" max="1024" step="10" onchange="document.getElementById('minWidth').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>最小上传高度 | 当前: </label><label id="minHeight"><?php echo $config['minHeight']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="minHeight" value="<?php echo $config['minHeight']; ?>" min="5" max="1024" step="10" onchange="document.getElementById('minHeight').innerHTML=value">
                </div>
                <h4 class="with-padding bg-success" style="text-align: center;">前端裁剪压缩 - 优点:服务器无压力 缺点:略增加用户端压力,仅支持JPG</h4>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="控制以下五项 不开启下边五项不生效">
                        <input type="hidden" name="imgRatio" value="0">
                        <input type="checkbox" name="imgRatio" value="1" <?php if ($config['imgRatio']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">前端修改图片</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>裁剪的宽度(0不生效) | 当前宽度: </label><label id="image_x"><?php echo $config['image_x']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="image_x" value="<?php echo $config['image_x']; ?>" min="0" max="4096" step="100" onchange="document.getElementById('image_x').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>裁剪的高度(0不生效) | 当前高度: </label><label id="image_y"><?php echo $config['image_y']; ?></label><label>px</label>
                    <input type="range" class="form-control" name="image_y" value="<?php echo $config['image_y']; ?>" min="0" max="4096" step="100" onchange="document.getElementById('image_y').innerHTML=value">
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="imgRatio_crop" value="0">
                        <input type="checkbox" name="imgRatio_crop" value="1" <?php if ($config['imgRatio_crop']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">上传前裁剪</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="imgRatio_preserve_headers" value="0">
                        <input type="checkbox" name="imgRatio_preserve_headers" value="1" <?php if ($config['imgRatio_preserve_headers']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">保留图片原始数据</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>前端压缩率(仅支持JPG) | 当前: </label><label id="imgRatio_quality"><?php echo $config['imgRatio_quality']; ?></label><label>%</label>
                    <input type="range" class="form-control" name="imgRatio_quality" value="<?php echo $config['imgRatio_quality']; ?>" min="10" max="100" step="5" onchange="document.getElementById('imgRatio_quality').innerHTML=value">
                </div>
                <h4 class="with-padding bg-blue" style="text-align: center;">后端压缩 - 优点:避免用户端欺骗,效果更好 缺点:增加服务器压力</h4>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="compress" value="0">
                        <input type="checkbox" name="compress" value="1" <?php if ($config['compress']) echo 'checked="checked"'; ?> data-toggle="tooltip" title=" 轻微有损压缩图片, 此压缩有可能使图片变大! 特别是小图片 也有一定概率改变图片方向">
                        <label style="font-weight: bold">后端压缩上传图片 | 更多图片格式的支持</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>后端压缩率 | 当前: </label><label id="compress_ratio"><?php echo $config['compress_ratio']; ?></label><label>%</label>
                    <input type="range" class="form-control" name="compress_ratio" value="<?php echo $config['compress_ratio']; ?>" min="1" max="100" step="1" onchange="document.getElementById('compress_ratio').innerHTML=value">
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content3">
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="ad_top" value="0">
                        <input type="checkbox" name="ad_top" value="1" <?php if ($config['ad_top']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">顶部广告</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>顶部广告内容 | 仅支持html代码</label>
                    <textarea class="form-control" rows="5" name="ad_top_info"><?php echo $config['ad_top_info']; ?></textarea>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="ad_bot" value="0">
                        <input type="checkbox" name="ad_bot" value="1" <?php if ($config['ad_bot']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">底部广告</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>底部广告内容 | 仅支持html代码</label>
                    <textarea class="form-control" rows="5" name="ad_bot_info"><?php echo $config['ad_bot_info']; ?></textarea>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
        <div class="tab-pane fade " id="Content4">
            <form class="form-inline" method="get" action="../application/del.php" id="form" name="delForm" onSubmit="getStr();" target="_blank">
                <p id="delimgurl"></p>
                <div class="form-group">
                    <label for="del">删除单张图片文件: </label>
                    <input type="url" name="url" class="form-control input-sm" id="del" placeholder="请输入图片链接">
                </div>
                <button type="submit" class="btn btn-sm btn-primary" onClick="return confirm('确认要删除？\n* 删除文件后将无法恢复! ');">删除单文件</button>
            </form>
            <form class="form-inline" action="<?php $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <label for="delDir" style="color:red">删除指定日期文件: </label>
                    <input type="text" class="form-control form-date input-sm" name="delDir" value="<?php echo date('Y/m/d/'); ?>" readonly="">
                </div>
                <button type="submit" class="btn btn-sm btn-danger" onClick="return confirm('确认要删除？\n* 删除文件夹后将无法恢复! ');">删除文件夹</button>
            </form>
            <form action="../application/compressing.php" method="post" target="_blank">
                <div class="form-group">
                    <label for="exampleInputInviteCode1">压缩文件夹</label>
                    <input type="text" class="form-control form-date input-sm" placeholder="" name="folder" value="<?php echo date('Y/m/d/'); ?>" readonly="">
                </div>
                <div class="radio-primary">
                    <input type="radio" name="type" value="Imgcompress" id="Imgcompress" checked="checked"><label for="Imgcompress" data-toggle="tooltip" title="压缩效率受后端压缩图片压缩率控制"> 使用本地</label>
                </div>
                <div class="radio-primary">
                    <input type="radio" name="type" value="TinyPng" id="TinyPng"><label for="TinyPng" data-toggle="tooltip" title="需要申请key,填入API设置的TinyPng Key中"> 使用TinyPng</label>
                </div>
                <label>* 已开启上传压缩的不需重复压缩! </label><br />
                <label>* 如果页面长时间没有响应,表示正面正在压缩! </label><br />
                <label>* 两种压缩均为不可逆,并且非常占用硬件资源. </label><br />
                <button type="submit" class="btn btn-mini btn-success">开始压缩</button>
            </form>
        </div>
        <div class="tab-pane fade " id="Content5">
            <h5>外部KEY</h5>
            <form class="form-condensed" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" style="margin-bottom: 10px;">
                <div class="form-group">
                    <label for="TinyPng" data-toggle="tooltip" title="申请网址"><a href="https://tinypng.com/developers" target="_blank">TinyPng Key &nbsp;</a></label>
                    <input type="text" class="form-control input-sm" id="TinyPng" name="TinyPng_key" value="<?php echo $config['TinyPng_key']; ?>" placeholder="填入压缩图片Key" data-toggle="tooltip" title="开启后会受服务器到https://tinypng.com 速度影响,国内不建议开启!" onkeyup="this.value=this.value.replace(/\s/g,'')">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <div class="form-group">
                    <label for="moderatecontent_key" data-toggle="tooltip" title="申请网址"><a href="https://client.moderatecontent.com" target="_blank">Moderate Key</a></label>
                    <input type="text" class="form-control input-sm" name="moderatecontent_key" id="moderatecontent_key" value="<?php echo $config['moderatecontent_key']; ?>" placeholder="填入图片鉴黄Key" data-toggle="tooltip" title="开启后会受服务器到https://moderatecontent.com 速度影响,国内不建议开启! " onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <div class="form-group">
                    <label for="nsfwjs_url" data-toggle="tooltip" title="nsfwjs github"><a href="https://github.com/infinitered/nsfwjs" target="_blank">nsfwjs url &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></label>
                    <input type="url" class="form-control input-sm" name="nsfwjs_url" id="nsfwjs_url" value="<?php echo $config['nsfwjs_url']; ?>" placeholder="http://ip:3307/nsfw?url=" data-toggle="tooltip" title="自行搭建nsfwjs服务的网站地址" onkeyup="this.value=this.value.replace(/\s/g,'')">
                </div>
                <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                <button type="submit" class="btn btn-mini btn-primary">保存</button>
            </form>
            <hr>
            <h5>上传Token</h5>
            <table class="table table-hover table-bordered table-condensed table-responsive visible-xs visible-sm" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>列表</th>
                        <th>添加时间</th>
                        <th>有效期至</th>
                        <th>禁用</th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($tokenList as $key => $value) :
                        $expired = $value['expired'] < time() ? '<p class="text-gray">已过期</p>' : '<p class="text-green">' . date('Y年m月d日 H:i:s', $value['expired']) . '</p>';
                    ?>
                        <tr>
                            <td><?php echo $value['id']; ?></td>
                            <td><?php echo $key; ?></td>
                            <td><?php echo date('Y年m月d日 H:i:s', $value['add_time']); ?></td>
                            <td><?php echo $expired; ?></td>
                            <td><a class="btn btn-mini btn-danger" href="admin.inc.php?stop_token=<?php echo $key; ?>">禁用</a>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div id="myDataGrid" class="datagrid visible-md visible-lg">
                <div class="input-control search-box search-box-circle has-icon-left has-icon-right" id="searchboxExample2" style="margin-bottom: 10px; max-width: 300px">
                    <input id="inputSearchExample2" type="search" class="form-control search-input input-sm" placeholder="搜索Token">
                    <label for="inputSearchExample2" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>
                    <a href="#" class="input-control-icon-right search-clear-btn"><i class="icon icon-remove"></i></a>
                </div>
                <div class="datagrid-container"></div>
            </div>

            <form class="form-inline" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" style="margin-top: 10px;">
                <div class="form-group">
                    <label for="exampleInputEmail3">Token: </label>
                    <input type="text" class="form-control input-sm" id="exampleInputEmail3" name="add_token" value="<?php echo privateToken(); ?>">
                </div>
                <div class="form-group">
                    <label for="exampleInputInviteCode3">有效期: </label>
                    <input type="number" class="form-control input-sm" id="exampleInputInviteCode3" name="add_token_expired" value="30">
                    <label for="exampleInputInviteCode3">天</label>
                </div>
                <input type="hidden" class="form-control" name="add_token_id" value="<?php echo count($tokenList); ?>" placeholder="隐藏的保存">
                <button type="submit" class="btn btn-sm btn-primary">添加</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content6">
            <form action="<?php $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" name="delDir" value="thumbnails/" data-toggle="tooltip" title="已缓存: <?php echo getFileNumber(APP_ROOT . $config['path'] . 'thumbnails/') . '文件 | 占用' . getDistUsed(getDirectorySize(APP_ROOT . $config['path'] . 'thumbnails/')); ?>" onClick="return confirm('确认要清理缓存？\n* 删除文件夹后将无法恢复! ');">清理缓存</button>
                </div>
            </form>
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <label data-toggle="tooltip" title="使用nsfwjs方式需要自行搭建或使用开源接口 据说准确率能达到93%">图片鉴黄</label>
                    <select class="chosen-select form-control" name="checkImg">
                        <option value="0" <?php if ($config['checkImg'] == 0) echo 'selected'; ?>>关闭</option>
                        <option value="1" <?php if ($config['checkImg'] == 1) echo 'selected'; ?>>moderatecontent | API 设置中填入Moderate Key</option>
                        <option value="2" <?php if ($config['checkImg'] == 2) echo 'selected'; ?> title="">nsfwjs | API 设置中填入nsfwjs url</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>图片违规判断率 | 当前: </label>
                    <label id="checkImg_value"><?php echo $config['checkImg_value']; ?></label><label>%</label>
                    <input type="range" class="form-control" name="checkImg_value" value="<?php echo $config['checkImg_value']; ?>" min="1" max="100" step="1" onchange="document.getElementById('checkImg_value').innerHTML=value">
                </div>
                <div class="form-group">
                    <label>缓存周期 | 当前: </label>
                    <label id="cache_freq"><?php echo $config['cache_freq']; ?></label><label>小时</label>
                    <input type="range" class="form-control" name="cache_freq" value="<?php echo $config['cache_freq']; ?>" min="1" step="1" max="24" onchange="document.getElementById('cache_freq').innerHTML=value">
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="check_ip" value="0">
                        <input type="checkbox" name="check_ip" value="1" <?php if ($config['check_ip']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">黑/白IP名单上传</label>
                    </div>
                    <textarea class="form-control" rows="5" name="check_ip_list" data-toggle="tooltip" title="每个IP以英文,结尾 支持IP段 例:123.23.23.44,193.134.*.*" placeholder=" 每个IP以英文,结尾 支持IP段 例:192.168.1.13,123.23.23.44,193.134.*.*"><?php echo $config['check_ip_list']; ?></textarea>
                    <label class="radio-inline"><input type="radio" name="check_ip_model" value="0" <?php if ($config['check_ip_model'] == 0) echo 'checked'; ?>> 黑名单模式</label>
                    <label class="radio-inline"><input type="radio" name="check_ip_model" value="1" <?php if ($config['check_ip_model'] == 1) echo 'checked'; ?>> 白名单模式</label>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="mustLogin" value="0">
                        <input type="checkbox" name="mustLogin" value="1" <?php if ($config['mustLogin']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">登录上传</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline">
                        <input type="hidden" name="apiStatus" value="0">
                        <input type="checkbox" name="apiStatus" value="1" <?php if ($config['apiStatus']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">API 上传</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="隐藏图片直链 | *注意: key与登录密码绑定,更改登录密码后key失效">
                        <input type="hidden" name="hide" value="0">
                        <input type="checkbox" name="hide" value="1" <?php if ($config['hide']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">原图保护</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="所有用户上传的图片使用加密链接删除后是否进入回收站">
                        <input type="hidden" name="image_recycl" value="0">
                        <input type="checkbox" name="image_recycl" value="1" <?php if ($config['image_recycl']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">图片回收</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="PHP扩展 | 安全设置 | 鉴黄 | 版本 检测">
                        <input type="hidden" name="checkEnv" value="0">
                        <input type="checkbox" name="checkEnv" value="1" <?php if ($config['checkEnv']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">网站检测</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="日志每月保存一个文件; 经测试二十万条数据并不影响速度!">
                        <input type="hidden" name="upload_logs" value="0">
                        <input type="checkbox" name="upload_logs" value="1" <?php if ($config['upload_logs']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">上传日志</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="通过指定参数查询图床的开放数据 | 与缓存周期同步 | 使用方法见使用手册->公共查询">
                        <input type="hidden" name="public" value="0">
                        <input type="checkbox" name="public" value="1" <?php if ($config['public']) echo 'checked'; ?>>
                        <label style="font-weight: bold">开放数据</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline" data-toggle="tooltip" title="<?php echo $config['domain']; ?>/api/public.php?show=time">
                        <input type="checkbox" name="public_list[]" value="time" id="time" <?php if (in_array('time', $config['public_list']))  echo 'checked'; ?>><label for="time">统计时间</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=today">
                        <input type="checkbox" name="public_list[]" value="today" id="today" <?php if (in_array('today', $config['public_list']))  echo 'checked'; ?>><label for="today">今日</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=yesterday">
                        <input type="checkbox" name="public_list[]" value="yesterday" id="yesterday" <?php if (in_array('yesterday', $config['public_list']))  echo 'checked'; ?>><label for="yesterday">昨日</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=total_space">
                        <input type="checkbox" name="public_list[]" value="total_space" id="total_space" <?php if (in_array('total_space', $config['public_list']))  echo 'checked'; ?>><label for="total_space">总空间</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=used_space">
                        <input type="checkbox" name="public_list[]" value="used_space" id="used_space" <?php if (in_array('used_space', $config['public_list']))  echo 'checked'; ?>><label for="used_space">已用</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=free_space">
                        <input type="checkbox" name="public_list[]" value="free_space" id="free_space" <?php if (in_array('free_space', $config['public_list']))  echo 'checked'; ?>><label for="free_space">剩余</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=image_used">
                        <input type="checkbox" name="public_list[]" value="image_used" id="image_used" <?php if (in_array('image_used', $config['public_list']))  echo 'checked'; ?>><label for="image_used">图片占用</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=file">
                        <input type="checkbox" name="public_list[]" value="file" id="file" <?php if (in_array('file', $config['public_list']))  echo 'checked'; ?>><label for="file">文件数量</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=dir">
                        <input type="checkbox" name="public_list[]" value="dir" id="dir" <?php if (in_array('dir', $config['public_list']))  echo 'checked'; ?>><label for="dir">文件夹数量</label>
                    </label>
                    <label class="checkbox-inline" data-toggle="tooltip" title="public.php?show=month">
                        <input type="checkbox" name="public_list[]" value="month" id="month" <?php if (in_array('month', $config['public_list']))  echo 'checked'; ?>><label for="month">最近30日</label>
                    </label>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content11">
            <h5>用户自己删除的会显示在这个页面</h5>
            <p>为了访问速度,仅显示最近20张图片; 图片回收需要在图床安全->图片回收中开启</p>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>缩略图</th>
                            <th>文件名</th>
                            <th>文件大小</th>
                            <th>文件操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 获取被隔离的文件
                        $cache_dir = APP_ROOT . $config['path'] . 'recycle/';                               // cache目录
                        $cache_file = get_file_by_glob($cache_dir . '*.*');                                 // 获取所有文件
                        @$cache_num = count($cache_file);                                                   // 统计目录文件个数
                        for ($i = 0; $i < $cache_num and $i < 21; $i++) :                                   // 循环输出文件
                            $file_cache_path = APP_ROOT . $config['path'] . 'recycle/' . $cache_file[$i];   // 图片绝对路径
                            $file_path =  $config['path'] . 'recycle/' . $cache_file[$i];                   // 图片相对路径
                            @$file_size =  getDistUsed(filesize($file_cache_path));                         // 图片大小
                            @$filen_name = $cache_file[$i];                                                 // 图片名称
                            $url = $config['imgurl'] . $config['path'] . 'recycle/' . $cache_file[$i];      // 图片网络连接
                            $unlink_img = $config['domain'] . '/application/del.php?url=' . $url;           // 图片删除连接
                        ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><img data-toggle="lightbox" src="<?php echo get_online_thumbnail($file_path); ?>" data-image="<?php echo $url; ?>" class="img-thumbnail"></td>
                                <td><?php echo $filen_name; ?></td>
                                <td><?php echo $file_size; ?></td>
                                <td>
                                    <a class="btn btn-mini" href="<?php echo $url; ?>" target="_blank">新窗口</a>
                                    <a class="btn btn-mini btn-success" href="?recycle_reimg=<?php echo $filen_name; ?>">恢复</a>
                                    <a class="btn btn-mini btn-danger" href="<?php echo $unlink_img; ?>" target="_blank">删除</a>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <form class="form-inline" action="<?php $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <input class="form-control" type="hidden" name="delDir" value="/suspic/" readonly="">
                <button class="btn btn-mini btn-danger"><?php echo $cache_num; ?>张 | 删除全部</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content7">
            <p>为了访问速度,仅显示最近20张图片;鉴黄需要在图床安全->图片鉴黄中开启</p>
            <p>key申请地址: <a href="https://client.moderatecontent.com/" target="_blank">https://client.moderatecontent.com/</a></p>
            <p>获得key后打开->API 设置->Moderate Key->填入 </p>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>缩略图</th>
                            <th>文件名</th>
                            <th>文件大小</th>
                            <th>文件操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 获取被隔离的文件
                        $cache_dir = APP_ROOT . $config['path'] . 'suspic/';                                // cache目录
                        $cache_file = get_file_by_glob($cache_dir . '*.*');                                 // 获取所有文件
                        @$cache_num = count($cache_file);                                                   // 统计目录文件个数
                        for ($i = 0; $i < $cache_num and $i < 21; $i++) :                                   // 循环输出文件
                            $file_cache_path = APP_ROOT . $config['path'] . 'suspic/' . $cache_file[$i];    // 图片绝对路径
                            $file_path =  $config['path'] . 'suspic/' . $cache_file[$i];                    // 图片相对路径
                            @$file_size =  getDistUsed(filesize($file_cache_path));                         // 图片大小
                            @$filen_name = $cache_file[$i];                                                 // 图片名称
                            $url = $config['imgurl'] . $config['path'] . 'suspic/' . $cache_file[$i];       // 图片网络连接
                            $unlink_img = $config['domain'] . '/application/del.php?url=' . $url;           // 图片删除连接
                        ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><img data-toggle="lightbox" src="<?php echo get_online_thumbnail($file_path); ?>" data-image="<?php echo $url; ?>" class="img-thumbnail"></td>
                                <td><?php echo $filen_name; ?></td>
                                <td><?php echo $file_size; ?></td>
                                <td>
                                    <a class="btn btn-mini" href="<?php echo $url; ?>" target="_blank">新窗口</a>
                                    <a class="btn btn-mini btn-success" href="?suspic_reimg=<?php echo $filen_name; ?>">恢复</a>
                                    <a class="btn btn-mini btn-danger" href="<?php echo $unlink_img; ?>" target="_blank">删除</a>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <form class="form-inline" action="<?php $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <input class="form-control" type="hidden" name="delDir" value="/suspic/" readonly="">
                <button class="btn btn-mini btn-danger"><?php echo $cache_num; ?>张 | 删除全部</button>
            </form>
        </div>
        <div class=" tab-pane fade" id="Content8">
            <div class="alert alert-primary">
                <h5>系统信息</h5>
                <hr />
                <p class="text-ellipsis">操作系统: <?PHP echo php_uname('s') . ' <small class="text-muted">' . php_uname() . '</small>'; ?></p>
                <p class="text-ellipsis">Web 服务: <?PHP echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                <p class="text-ellipsis">服务器IP: <?PHP echo  GetHostByName($_SERVER['SERVER_NAME']) ?></p>
                <p class="text-ellipsis">系统时间: <?PHP echo date("Y-m-d G:i:s"); ?></p>
                <p class="text-ellipsis">已用磁盘: <?php echo  getDistUsed(disk_total_space(__DIR__) - disk_free_space(__DIR__)) ?></p>
                <p class="text-ellipsis">剩余磁盘: <?php echo  getDistUsed(disk_free_space(__DIR__)); ?></p>
                <h5>PHP信息</h5>
                <hr />
                <p class="text-ellipsis">PHP: <?php echo  phpversion(); ?></p>
                <p class="text-ellipsis">GD : <?php echo (gd_info()["GD Version"]); ?></p>
                <p class="text-ellipsis">PHP最大上传: <?PHP echo get_cfg_var("upload_max_filesize"); ?></p>
                <p class="text-ellipsis">POST最大上传: <?php echo ini_get('post_max_size'); ?></p>
                <p class="text-ellipsis">PHP最长执行时间: <?PHP echo get_cfg_var("max_execution_time") . "秒 "; ?></p>
                <p class="text-ellipsis">PHP允许占用内存: <?PHP echo get_cfg_var("memory_limit"); ?></p>
                <h5>我的信息</h5>
                <hr />
                <p class="text-ellipsis">浏览器: <?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
                <p class="text-ellipsis">登录IP: <?php echo  $_SERVER["REMOTE_ADDR"]; ?></p>
                <h5>图床信息</h5>
                <hr />
                <p class="text-ellipsis">
                    <?php if (empty($config['TinyPng_key'])) : ?>
                        <i class="icon icon-times" data-toggle="tooltip" title="图片压缩TinyPng未填写">TinyPng</i><br />
                    <?php else : ?>
                        <i class="icon icon-check" data-toggle="tooltip" title="图片压缩TinyPng已填写">TinyPng</i><br />
                    <?php endif; ?>
                    <?php if (empty($config['moderatecontent_key'])) : ?>
                        <i class="icon icon-times" data-toggle="tooltip" title="图片审查moderatecontent未填写">moderatecontent</i><br />
                    <?php else : ?>
                        <i class="icon icon-check" data-toggle="tooltip" title="图片审查moderatecontent已填写">Moderatecontent</i><br />
                    <?php endif; ?>
                </p>
                <p>
                    <span class="label label-badge label-info">当前版本:<?php echo $config['version']; ?></span>
                    <a href="https://github.com/icret/EasyImages2.0/releases" target="_blank"><span class="label label-badge label-success" data-toggle="tooltip" title="更新后删除<p>/admin/logs/verson/</p>文件夹会自动同步版本">最新版本:<?php echo getVersion(); ?></span></a>
                </p>
            </div>
        </div>
        <div class="tab-pane fade" id="Content9">
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">
                <div class="form-group">
                    <label data-toggle="tooltip" title="配色样式文件夹位置: /public/static/zui/theme/">网站配色</label>
                    <select class="chosen-select form-control" name="theme">
                        <option value="default" <?php if ($config['theme'] == 'default') echo 'selected'; ?>>默认配色</option>
                        <option value="red" <?php if ($config['theme'] == 'red') echo 'selected'; ?>>红色</option>
                        <option value="green" <?php if ($config['theme'] == 'green') echo 'selected'; ?>>绿色</option>
                        <option value="blue" <?php if ($config['theme'] == 'blue') echo 'selected'; ?>>蓝色</option>
                        <option value="bluegrey" <?php if ($config['theme'] == 'bluegrey') echo 'selected'; ?>>蓝灰</option>
                        <option value="indigo" <?php if ($config['theme'] == 'indigo') echo 'selected'; ?>>靛青</option>
                        <option value="brown" <?php if ($config['theme'] == 'brown') echo 'selected'; ?>>棕色</option>
                        <option value="yellow" <?php if ($config['theme'] == 'yellow') echo 'selected'; ?>>黄色</option>
                        <option value="purple" <?php if ($config['theme'] == 'purple') echo 'selected'; ?>>紫色</option>
                        <option value="black" <?php if ($config['theme'] == 'black') echo 'selected'; ?>>黑色</option>
                    </select>
                </div>
                <div class="form-group">
                    <label data-toggle="tooltip" title="暂支持中文简繁体转换">界面语言</label>
                    <select class="chosen-select form-control" name="language">
                        <option value="0" <?php if ($config['language'] == '0') echo 'selected'; ?>>简体中文</option>
                        <option value="1" <?php if ($config['language'] == '1') echo 'selected'; ?>>繁體中文</option>
                    </select>
                </div>
                <div class="form-group">
                    <h5>上传首选显示</h5>
                    <label class="radio-inline">
                        <input type="radio" name="upload_first_show" value="1" data-toggle="tooltip" title="图片直链" <?php if ($config['upload_first_show'] == 1) echo 'checked'; ?>>
                        <i class="icon icon-link"></i>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="upload_first_show" value="2" data-toggle="tooltip" title="论坛代码" <?php if ($config['upload_first_show'] == 2) echo 'checked'; ?>>
                        <i class="icon icon-chat"></i>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="upload_first_show" value="3" data-toggle="tooltip" title="Markdown" <?php if ($config['upload_first_show'] == 3) echo 'checked'; ?>>
                        <i class="icon icon-code"></i>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="upload_first_show" value="4" data-toggle="tooltip" title="HTML" <?php if ($config['upload_first_show'] == 4) echo 'checked'; ?>>
                        <i class="icon icon-html5"></i>
                    </label>
                    <label class="radio-inline" data-toggle="tooltip" title="删除链接">
                        <input type="radio" id="upload_first_show5" name="upload_first_show" value="5" <?php if ($config['upload_first_show'] == 5) echo 'checked'; ?>>
                        <i class="icon icon-trash"></i>
                    </label>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="删除链接是经过加密的">
                        <input type="hidden" name="show_user_hash_del" value="0">
                        <input type="checkbox" name="show_user_hash_del" value="1" <?php if ($config['show_user_hash_del']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">上传后显示删除链接</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title=" 关闭后非登录状态不显示图片">
                        <input type="hidden" name="showSwitch" value="0">
                        <input type="checkbox" name="showSwitch" value="1" <?php if ($config['showSwitch']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">广场</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="广场图片以上传时间倒序 | 正序">
                        <input type="hidden" name="showSort" value="0">
                        <input type="checkbox" name="showSort" value="1" <?php if ($config['showSort']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">排序</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="广场图片信息按钮">
                        <input type="hidden" name="show_exif_info" value="0">
                        <input type="checkbox" name="show_exif_info" value="1" <?php if ($config['show_exif_info']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">Exif</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch switch-inline" data-toggle="tooltip" title="图片过多时可能会影响统计时间">
                        <input type="hidden" name="chart_on" value="0">
                        <input type="checkbox" name="chart_on" value="1" <?php if ($config['chart_on']) echo 'checked="checked"'; ?>>
                        <label style="font-weight: bold">统计</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>默认游客浏览数量 | 当前: </label>
                    <label id="listNumber"><?php echo $config['listNumber']; ?>张</label>

                    <input type="range" class="form-control" name="listNumber" value="<?php echo $config['listNumber']; ?>" min="10" max="100" step="10" onchange="document.getElementById('listNumber').innerHTML=value" data-toggle="tooltip" title="可在网址后填写参数实时更改预览数量 如: https://png.cm/application/list.php?num=3">
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
        <div class="tab-pane fade" id="Content10">
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" onsubmit="return md5_post()">
                <h5>管理员账号管理 | 不变就不要保存更改</h5>
                <div class="form-group">
                    <div class="input-control has-icon-left">
                        <input type="text" name="user" id="account" class="form-control" value="<?php echo $config['user']; ?>" placeholder="更改管理账号" onkeyup="this.value=this.value.replace(/\s/g,'')">
                        <label for="account" class="input-control-icon-left"><i class="icon icon-user "></i></label>
                    </div>
                    <div class="input-control has-icon-left" style="margin-top: 10px;" data-toggle="tooltip" title="当前显示的是经过MD5加密的">
                        <input type="text" name="password" id="password" class="form-control" value="<?php echo $config['password']; ?>" placeholder="更改管理密码" onkeyup="this.value=this.value.replace(/\s/g,'')">
                        <input type="hidden" name="password" id="md5_password">
                        <label for="password" class="input-control-icon-left"><i class="icon icon-key"></i></label>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="update" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn btn-primary">更改管理 账号/密码</button>
                <div class="alert alert-primary with-icon col-xs-8" style="margin-top: 5px;">
                    <i class="icon-info-sign"></i>
                    <div class="content">
                        <p>直接输入账号和密码即可完成修改.</p>
                        <p>更改后会立即生效并重新登录,请务必牢记账号和密码! </p>
                        <p>如果忘记账号可以打开-><code>/config/config.php</code>文件->找到user对应的键值->填入</p>
                        <p>如果忘记密码请将密码->转换成MD5小写-><a href="https://md5jiami.bmcx.com/" target="_blank" class="text-purple">转换网址</a>->打开<code>/config/config.php</code>文件->找到password对应的键值->填入</p>
                    </div>
                </div>
            </form>
            <!-- 上传用户管理 start-->
            <hr>
            <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" onsubmit="return uploader_md5_post()">
                <h5>上传者账号管理 | 开启登录上传后可添加</h5>
                <div class="form-group">
                    <div class="input-control has-icon-left" data-toggle="tooltip" title="上传者账号只能上传不能操作其他项目">
                        <input type="text" name="uploader_user" id="account" class="form-control" value="" required="required" placeholder="添加上传者账号" onkeyup="this.value=this.value.replace(/\s/g,'')">
                        <label for="account" class="input-control-icon-left"><i class="icon icon-user "></i></label>
                    </div>
                    <div class="input-control has-icon-left" style="margin-top: 10px;">
                        <input type="text" name="uploader_password" id="uploader_password" class="form-control" value="" required="required" placeholder="添加/更改 上传者密码" onkeyup="this.value=this.value.replace(/\s/g,'')">
                        <input type="hidden" name="uploader_password" id="uploader_md5_password">
                        <label for="password" class="input-control-icon-left"><i class="icon icon-key"></i></label>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="uploader_form" value="<?php echo date("Y-m-d H:i:s"); ?>" placeholder="隐藏的保存">
                </div>
                <button type="submit" class="btn btn btn-danger">添加上传 账号/密码</button>
                <div class="alert alert-primary with-icon col-xs-8" style="margin-top: 5px;">
                    <i class="icon-info-sign"></i>
                    <div class="content">
                        <p>开启登录上传后,可以添加一些只能上传的账号.</p>
                        <p>更改后会立即生效并重新登录,请将账号和密码发给使用者.</p>
                        <p>上传用户的配置文件在<code>config.guest.php</code></p>
                        <p>如果忘记密码请填入账号并填写新的密码即可更正密码 | <b class="text-success">与更改管理 账号/密码不同!</b></p>
                    </div>
                </div>
            </form>
            <h5>当前所有上传者账号与密码 | 所有密码均经过MD5加密 | 账号<code>guest</code>的MD5为: <code>084e0343a0486ff05530df6c705c8bb4</code></h5>
            <div class="table-responsive">
                <table class="table table-condensed table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>登录账号</th>
                            <th>登录密码</th>
                            <th>删除密码</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guestConfig as $key => $value) : ?>
                            <tr>
                                <td><?php echo $key; ?></td>
                                <td><?php echo $value; ?></td>
                                <td><a class='btn btn-mini btn-danger' href='admin.inc.php?delete_guest=<?php echo $key; ?>'>删除</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php static_cdn(); ?>/public/static/jscolor/jscolor.min.js"></script>
<link href="<?php static_cdn(); ?>/public/static/zui/lib/datetimepicker/datetimepicker.min.css" rel="stylesheet">
<script src="<?php static_cdn(); ?>/public/static/zui/lib/datetimepicker/datetimepicker.min.js"></script>
<script src="<?php static_cdn(); ?>/public/static/md5/md5.min.js"></script>
<link href="<?php static_cdn(); ?>/public/static/zui/lib/datagrid/zui.datagrid.min.css" rel="stylesheet">
<script src="<?php static_cdn(); ?>/public/static/zui/lib/datagrid/zui.datagrid.min.js"></script>
<script>
    //
    jscolor.presets.myPreset = {
        value: '<?php echo $config['textColor']; ?>',
        format: 'rgba',
        closeText: true,
        width: 201,
        height: 81,
        backgroundColor: '#333',
        palette: [
            '#000000', '#7d7d7d', '#870014', '#ec1c23', '#ff7e26',
            '#fef100', '#22b14b', '#00a1e7', '#3f47cc', '#a349a4',
            '#ffffff', '#c3c3c3', '#b87957', '#feaec9', '#ffc80d',
            '#eee3af', '#b5e61d', '#99d9ea', '#7092be', '#c8bfe7',
        ],
    }

    // 使用本地存储记录当前tab页面
    $('[data-tab]').on('shown.zui.tab', function(e) {
        var cookie_value = e.delegateTarget.attributes[1].value;
        $.zui.store.pageSet('data-tab-now', cookie_value);
        console.log('当前被激活的标签页', e.target);
        console.log('上一个标签页', e.relatedTarget);
    })
    // cookie有
    if ($.zui.store.pageGet('data-tab-now') != null) {
        $ac = $.zui.store.pageGet('data-tab-now');
        $("a[href = '" + $ac + "']").parent().addClass("active in")
        $($ac).addClass("active in")
    }
    // cookie无
    if ($.zui.store.pageGet('data-tab-now') == null) {
        $("a[href = '#Content1']").parent().addClass("active in")
        $('#Content1').addClass("active in")
    }

    // tips提示
    $('[data-toggle="tooltip"]').tooltip({
        tipClass: 'tooltip',
        placement: 'top',
        html: true,
        delay: {
            show: 50,
            hide: 100
        }
    });
    // 账号密码 | 以md5加密方式发送
    function uploader_md5_post() {
        var password = document.getElementById('uploader_password');
        var md5pwd = document.getElementById('uploader_md5_password');
        md5pwd.value = md5(password.value);
        //可以校验判断表单内容,true就是通过提交,false,阻止提交
        return true;
    }
    // 账号密码 | 以md5加密方式发送
    function md5_post() {
        var password = document.getElementById('password');
        var md5pwd = document.getElementById('md5_password');
        md5pwd.value = md5(password.value);
        //可以校验判断表单内容,true就是通过提交,false,阻止提交
        return true;
    }
    // 动态显示要删除的图片
    var oBtn = document.getElementById('del');
    var oTi = document.getElementById('title');
    if ('oninput' in oBtn) {
        oBtn.addEventListener("input", getWord, false);
    } else {
        oBtn.onpropertychange = getWord;
    }

    function getWord() {
        var delimgurl = document.getElementById("delimgurl");
        delimgurl.innerHTML += '<img src="' + oBtn.value + '" width="200" class="img-rounded" /><br />';
    }
    // 日期选择
    $(".form-date").datetimepicker({
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 0,
        format: "yyyy/mm/dd/",
        endDate: new Date() // 只能选当前日期之前
    });

    // Token 数据表格
    $('#myDataGrid').datagrid({
        dataSource: {
            cols: [{
                    name: 'id',
                    label: 'ID',
                    width: 0.1
                },
                {
                    name: 'list',
                    label: '列表',
                    width: 0.4
                },
                {
                    name: 'add_time',
                    label: '添加时间',
                    html: true,
                    width: 0.2
                },
                {
                    name: 'expired',
                    label: '有效期至',
                    html: true,
                    width: 0.2
                },
                {
                    name: 'delete',
                    label: '删除',
                    html: true,
                    width: 0.1
                },
            ],
            array: [
                <? foreach ($tokenList as $key => $value) :
                    $expired = $value['expired'] < time() ? '<p class="text-gray">已过期</p>' : '<p class="text-green">' . date('Y年m月d日 H:i:s', $value['expired']) . '</p>'; ?> {
                        id: '<?php echo $value['id']; ?>',
                        list: '<?php echo $key; ?>',
                        add_time: '<?php echo date('Y年m月d日 H:i:s', $value['add_time']); ?>',
                        expired: '<?php echo $expired; ?>',
                        delete: "<a class='btn btn-mini btn-danger' href='admin.inc.php?stop_token=<?php echo $key; ?>'>禁用</a>"
                    },
                <?php endforeach; ?>
            ]
        },
        sortable: true,
        hoverCell: true,
        showRowIndex: false,
        responsive: true,
        // ... 其他初始化选项
    });
    // 获取数据表格实例
    var myDataGrid = $('#myDataGrid').data('zui.datagrid');

    // 按照 `name` 列降序排序
    myDataGrid.sortBy('expired', 'desc');

    // 更改网页标题
    document.title = "图床设置 - <?php echo $config['title']; ?>"
</script>
<?php require_once APP_ROOT . '/application/footer.php';
/** 引入设置页面检测文件 */ if ($config['checkEnv']) require_once APP_ROOT . '/application/check_admin.inc.php'; ?>