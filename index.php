<?php
/**
 * @package hacpai-sync-wordpress
 * @version 0.8
 */
/*
Plugin Name: Hacpai Sync Wordpress
Plugin URI: http://wordpress.org/plugins/hacpai-sync-wordpress/
Description: 同步您的博客内容到黑客派社区
Author: zonghua
Version: 0.8
Author URI: http://applehater.cn/
*/

require 'config.php';

define('URL_ARTICLE', 'http://rhythm.b3log.org/api/article');
define('URL_COMMENT', 'http://rhythm.b3log.org/api/comment');

class Comment
{
    var $id;
    var $articleId;
    var $content;
    var $authorName;
    var $authorEmail;
}

class Article
{
    var $id;
    var $title;
    var $permalink;
    var $tags;
    var $content;
}


function http_post($URL, $data)
{
    $ch = curl_init($URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    $result = curl_exec($ch);
    return $result;
}

function post2article($post)
{
    $article = new Article();
    $article->id = $post->ID;
    //$article->id = '1165070220000';
    $article->title = $post->post_title;
    $article->permalink = '/' . $post->post_title;
    $article->content = $post->post_content;
    $article->tags = 'API';
    return $article;
}

function commentdata2comment($commentdata)
{
    $comment = new Comment();
    $comment->articleId = $commentdata['comment_post_ID'];
    //$comment->articleId = '1165070220000';
    $comment->content = $commentdata['comment_content'];
    $comment->authorName = $commentdata['comment_author'];
    $comment->authorEmail = $commentdata['comment_author_email'];
    return $comment;

}

function test($obj)
{
    echo json_encode($obj);
    exit();
}

/**
 * 发布文章
 * @param  int $post_id 文章编号
 * @param  文章 $post wordpress的文章
 */
function post_article($post_id, $post)
{
    $article = post2article($post);
    $data = array('article' => $article,
        'client' => $GLOBALS['client']);

    $response = http_post(URL_ARTICLE, json_encode($data));
}

add_action('publish_post', 'post_article', 10, 2);

/**
 * 更新文章
 * @param  int $post_id 文章编号
 * @param  文章 $post 带有状态属性
 * @param  boolean $update 是否更新了
 */
function update_article($post_id, $post, $update)
{
    if ($post->post_status !== 'draft') {//如果不是草稿
        $article = post2article($post);
        $data = array('article' => $article,
            'client' => $GLOBALS['client']);
        $response = http_post(URL_ARTICLE, json_encode($data));
    }

}

add_action('wp_insert_post', 'update_article', 10, 3);

/**
 * 发表评论
 * @param  array $commentdata 回复的数据
 */
function post_comment($commentdata)
{
    $comment = commentdata2comment($commentdata);
    $data = array('comment' => $comment,
        'client' => $GLOBALS['client']);
    $response = http_post(URL_COMMENT, json_encode($data));
    return $commentdata;
}

add_filter('preprocess_comment', 'post_comment');

