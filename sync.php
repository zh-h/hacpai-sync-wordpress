<?php
require 'config.php';
require '../../../wp-includes/comments.php';

/**
 * 同步社区评论到客户端
 * @param  json $obj 评论数据
 */
function sync_comment($obj)
{
    $data = json_decode($obj);
    $comment = $data['comment'];
    $key = $data['client']['key'];
    if ($key = $GLOBALS['client']['key']) {
        $commentdata = array(
            'comment_post_ID' => $comment['articleId'],
            'comment_author' => $comment['authorName'],
            'comment_author_email' => $comment['authorEmail'],
            'comment_author_url' => $comment['authorURL'],
            'comment_content' => $comment['content'],
            'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
            'comment_parent' => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
            'user_id' => 0, //passing current user ID or any predefined as per the demand
        );
        //Insert new comment and get the comment ID
        $comment_id = wp_new_comment($commentdata);
    } else {
        echo 'Key not match';
    }

}

function main()
{
    sync_comment($_POST);
}

main();