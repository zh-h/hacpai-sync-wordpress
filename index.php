<?php
/*
Plugin Name: sync-hacpai
Plugin URI: https://github.com/zh-h/hacpai-sync-wordpress
Description: 同步您的博客内容到黑客派社区
Author: zonghua, kinosang, zhaofeng-shu33
Version: 1.40
Author URI: http://applehater.cn/
 */
if ( ! defined( 'ABSPATH' ) ) exit;
require 'setting.php';

define('SYNC_HACPAI_URL_ARTICLE', 'https://rhythm.b3log.org/api/article');
define('SYNC_HACPAI_URL_COMMENT', 'https://rhythm.b3log.org/api/comment');

$client = array(
    'title' => esc_attr(get_option('title')), //博客抬头
    'host'  => esc_attr(get_option('host')), //博客域名
    'userName' => esc_attr(get_option('user')), //需要和 hacpai 的账户一致
    'name' => 'WordPress',
    'ver' => get_bloginfo( 'version' ),    
    'userB3Key'   => esc_attr(get_option('key')), //在 https://hacpai.com/settings#b3 进行设置
);

class Sync_Hacpai_Comment
{
    public $id;
    public $articleId;
    public $content;
    public $authorName;
    public $parentId;
}

class Sync_Hacpai_Article
{
    public $id;
    public $title;
    public $permalink;
    public $tags;
    public $content;
}

function sync_hacpai_http_post($URL, $data)
{
    $result = wp_safe_remote_post($URL, array('body' => $data));
    if(is_wp_error($result))
        return $result->get_error_messages();
    return $result['body'];
}

function sync_hacpai_error_logging($data, $function_name = '')
{
    $content = gmdate("M d Y H:i:s", time()) . ' @ ' . $function_name . ' : ' . $data;
    error_log($content);
}

function sync_hacpai_post2article($post)
{
    $article = new Sync_Hacpai_Article();
    $article->id = $post->ID;
    $article->title = $post->post_title;
    $article->permalink = '/?p=' . $post->ID;
    $article->content = $post->post_content;

    $tag_array = array('API', 'B3log');
    $tags = get_the_tags($post->ID);
    if($tags){
        foreach ($tags as $tag) {
            $tag_array[] = $tag->name;
        }
        $article->tags = implode(', ', $tag_array);

    }
    else{
        $article->tags = '未分类';
    }
    return $article;
}

function sync_hacpai_commentdata2comment($comment_ID, $commentdata)
{
    $comment = new Sync_Hacpai_Comment();
    $comment->id = $comment_ID;
    $comment->articleId = $commentdata['comment_post_ID'];
    $comment->content = $commentdata['comment_content'];
    $comment->authorName = $commentdata['comment_author'];
    $comment->parentId = '';
    return $comment;
}

/**
 * 发布文章
 * @param  int $post_id 文章编号
 * @param  文章 $post wordpress的文章
 */
function sync_hacpai_post_article($post_id, $post)
{
    if (get_option('post_article') == '1') {
        //同步发表文章没有关闭
        $article = sync_hacpai_post2article($post);
        $data = array(
            'article' => $article,
            'client' => $GLOBALS['client'],
        );

        $response = sync_hacpai_http_post(SYNC_HACPAI_URL_ARTICLE, json_encode($data));
    }
}

add_action('publish_post', 'sync_hacpai_post_article', 10, 2);

/**
 * 更新文章
 * @param  int $post_id 文章编号
 * @param  文章 $post 带有状态属性
 * @param  boolean $update 是否更新了
 */
function update_article($post_id, $post, $update)
{
    if (get_option('update_article') == '1') {
        //同步更新文章没有关闭
        if ($post->post_status !== 'draft' && $post->post_status !== 'auto-draft') {
            //如果不是草稿并且不是新建文章的自动草稿
            $article = sync_hacpai_post2article($post);
            $data = array(
                'article' => $article,
                'client' => $GLOBALS['client'],
            );
            $response = sync_hacpai_http_post(SYNC_HACPAI_URL_ARTICLE, json_encode($data));
        }
    }
}

add_action('wp_insert_post', 'update_article', 10, 3);

/**
 * 发表评论
 * @param  array $commentdata 回复的数据
 * @since WordPress 4.5.0 The $commentdata parameter was added.
 */
function sync_hacpai_post_comment($comment_ID, $comment_approved, $commentdata)
{
    if (get_option('post_comment') == '1' && $comment_approved) {
        //同步评论没有关闭
        $comment = sync_hacpai_commentdata2comment($comment_ID, $commentdata);
        $data = array(
            'comment' => $comment,
            'client' => $GLOBALS['client'],
        );
        $response = sync_hacpai_http_post(SYNC_HACPAI_URL_COMMENT, json_encode($data));
    }
    return $commentdata;
}

add_action('comment_post', 'sync_hacpai_post_comment', 10, 3);

/**
 * 黑客派同步到博客
 * 指定参数 hacpai-api = sync-comment
 */
function sync_hacpai_sync_comment()
{
    if (isset($_GET['hacpai-api']) && $_GET['hacpai-api'] === 'sync-comment') {
        //判断是不是同步的接口
        if (get_option('sync_comment') == '1') {
            //开启了社区评论同步到博客
            $data = json_decode(file_get_contents("php://input"));
            $comment = $data->comment;
            $key = $data->client->key;
            if ($key == $GLOBALS['client']['userB3Key']) {
                //判断是否配置了正确的key
                $commentdata = array(
                    'comment_post_ID'      => $comment->articleId,
                    'comment_author'       => $comment->authorName,
                    'comment_author_url'   => $comment->authorURL,
                    'comment_content'      => $comment->contentHTML,
                    'comment_type'         => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
                    'comment_parent'       => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
                    'user_id'              => 0, //passing current user ID or any predefined as per the demand
                    'comment_author_IP'    => $comment->ip,
                    'comment_agent'        => 'Hacpai/B3log Sync',
                    'comment_date'         => current_time('mysql'),
                    'comment_approved'     => 1,
                );
                //Insert new comment and get the comment ID
                $comment_id = wp_insert_comment($commentdata);
                exit(json_encode($comment_id));
            } else {
                sync_hacpai_error_logging('Key not match', 'sync_hacpai_sync_comment');                
                exit('Key not match');
            }
        } else {
            sync_hacpai_error_logging('Method not allowed', 'sync_hacpai_sync_comment');
            exit('Method not allowed');
        }
    }
}

add_action('template_redirect', 'sync_hacpai_sync_comment');
