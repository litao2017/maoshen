<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
//全局配置变量
  
return [
    'url_route_on' => true,
    //'response_exit'=>false,
   /* 'log'          => [
        'type' => 'trace', // 支持 socket trace file
    ],*/
   // 'app_trace' =>  true,
    'session'                => [
    'prefix'         => 'admin',
    'type'           => '',
    'auto_start'     => true,
],
	
	
    'template'               => [
	
		 'tpl_begin'    => '<{',
        // 模板引擎普通标签结束标记
       	 'tpl_end'      => '}>',
        // 标签库标签开始标记
		
	]
];
