<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]


// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//根目录
define('ROOT_PATH', __DIR__ . '/');

// 开启调试模式
define('APP_DEBUG', false);
//扩展类目录
define('EXTEND_PATH', __DIR__ . '/../extend/');
define('WAP_CSS_URL', '/static/wap/css/');
define('WAP_JS_URL', '/static/wap/js/');
define('WAP_IMAGE_URL', '/static/wap/image/');
define('VENDOR_URL', '/vendor/');


define('ADMIN_CSS_URL', '/static/admin/css/');
define('ADMIN_JS_URL', '/static/admin/js/');
define('ADMIN_IMAGE_URL', '/static/admin/image/');
define('IMG_UPLOAD', '/upload/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
