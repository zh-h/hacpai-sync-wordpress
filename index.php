<?php
/**
 * @package hacpai-sync-wordpress
 * @version 1.0
 */
/*
Plugin Name: Hacpai Sync Wordpress
Plugin URI: http://wordpress.org/plugins/hacpai-sync-wordpress/
Description: 同步您的博客内容到黑客派社区
Author: zonghua
Version: 1.0
Author URI: http://applehater.cn/
*/

require 'setting.php';

define('URL_ARTICLE', 'http://rhythm.b3log.org/api/article');
define('URL_COMMENT', 'http://rhythm.b3log.org/api/comment');

$client = array('title' => esc_attr(get_option('title')), //博客抬头
    'host' => esc_attr(get_option('host')), //博客域名
    'email' => esc_attr(get_option('email')), //需要和 hacpai 的账户一致
    'key' => esc_attr(get_option('key'))); //在 https://hacpai.com/settings#soloKey 进行设置
$sync_category = esc_attr(get_option('sync_category'));//同步选项 {'0':'关闭';'1':'博客到黑客派';'2':'双向'}

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
    if ($GLOBALS['sync_category'] != '0') {//同步没有关闭

        $article = post2article($post);
        $data = array('article' => $article,
            'client' => $GLOBALS['client']);

        $response = http_post(URL_ARTICLE, json_encode($data));
    }
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
    if ($GLOBALS['sync_category'] != '0') {//同步没有关闭

        if ($post->post_status !== 'draft' && $post->post_status !== 'auto-draft') {//如果不是草稿并且不是新建文章的自动草稿
            $article = post2article($post);
            $data = array('article' => $article,
                'client' => $GLOBALS['client']);
            $response = http_post(URL_ARTICLE, json_encode($data));
        }
    }
}

add_action('wp_insert_post', 'update_article', 10, 3);

/**
 * 发表评论
 * @param  array $commentdata 回复的数据
 */
function post_comment($commentdata)
{
    if ($GLOBALS['sync_category'] != '0') {//同步没有关闭

        $comment = commentdata2comment($commentdata);
        $data = array('comment' => $comment,
            'client' => $GLOBALS['client']);
        $response = http_post(URL_COMMENT, json_encode($data));
    }
    return $commentdata;
}

add_filter('preprocess_comment', 'post_comment');

/**
 * 黑客派同步到博客
 * 指定参数 hacpai-api = sync-comment
 */
function sync_comment()
{
    if ($_GET['hacpai-api'] === 'sync-comment') {//判断是不是同步的接口
        if ($GLOBALS['sync_category'] == '2') {//是双向同步的设置
            $data = json_decode(file_get_contents("php://input"));
            $comment = $data->comment;
            $key = $data->client->key;
            if ($key == $GLOBALS['client']['key']) {//判断是否配置了正确的key
                $commentdata = array(
                    'comment_post_ID' => $comment->articleId,
                    'comment_author' => $comment->authorName,
                    'comment_author_email' => $comment->authorEmail,
                    'comment_author_url' => $comment->authorURL,
                    'comment_content' => $comment->content,
                    'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
                    'comment_parent' => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
                    'user_id' => 0, //passing current user ID or any predefined as per the demand
                );
                //Insert new comment and get the comment ID
                $comment_id = wp_new_comment($commentdata);
                test($comment_id);
            }
        } else {
            echo 'Key not match';
        }
    }
}

add_action('template_redirect', 'sync_comment');
