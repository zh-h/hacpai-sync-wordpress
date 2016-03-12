<?php
/* 填写配置 */
$client = array('title' => '我的个人博客', //博客抬头
    'host' => 'http://xxx.com', //博客域名
    'email' => 'xxoo@outlook.com', //需要和 hacpai 的账户一致
    'key' => 'zonghua'); //在 https://hacpai.com/settings#soloKey 进行设置

/**
 * 评论同步接口
 * 在 https://hacpai.com/settings#soloCmtURL 配置
 * 参数为 YOUR_BLOG_URI/wp-content/plugins/hacpai-sync-wordpress/sync.php
 */
