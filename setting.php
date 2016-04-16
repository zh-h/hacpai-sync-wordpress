<?php

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu()
{
    add_menu_page('Hacpai Sync Wordpress Options', 'Hacpai Sync Wordpress Plugin', 'administrator', __FILE__, 'hacpai_sync_wordpress_setting_page', plugins_url('/images/icon.png', __FILE__));
    add_action('admin_init', 'register_sync_setting');
}

function register_sync_setting()
{
    register_setting('hacpai-sync-wordpress-setting_options_group', 'title');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'host');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'email');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'key');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'post_article');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'update_article');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'post_comment');
    register_setting('hacpai-sync-wordpress-setting_options_group', 'sync_comment');
}

function hacpai_sync_wordpress_setting_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $sync_category = esc_attr(get_option('sync_category'));
    ?>
    <div class="wrap">
        <h1>撰写设置</h1>
        <div>
            <div style="margin-left: 19%">
                <img src="<?=plugins_url('/images/logo.png', __FILE__)?>">
            </div>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields('hacpai-sync-wordpress-setting_options_group');?>
            <?php do_settings_sections('hacpai-sync-wordpress-setting_options_group');?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="title">站点名</label></th>
                    <td>
                        <input name="title" type="text" id="title" value="<?=esc_attr(get_option('title'))?>" class="regular-text ltr">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="host">域名</label></th>
                    <td>
                        <input name="host" type="text" id="host" value="<?=esc_attr(get_option('host'))?>" class="regular-text ltr">
                        <p class="description" id="host-description">不需要填写 http:// 或者 https:// </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="email">邮箱</label></th>
                    <td>
                        <input name="email" type="text" id="email" value="<?=esc_attr(get_option('email'))?>" class="regular-text ltr">
                        <p class="description" id="email-description">这个电子邮件地址需要和你在黑客派账户的邮箱一致</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="key">密钥 (key)</label></th>
                    <td>
                        <input name="key" type="text" id="key" value="<?=esc_attr(get_option('key'))?>" class="regular-text ltr">
                        <p class="description" id="key-description">请在这里设置查看你的密钥 <a href="https://hacpai.com/settings#soloKey" target="_blank">hacpai.com/settings</a></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sync_category">选择同步</label></th>
                    <td>
                       <fieldset>
                           <p>
                           <label for="post_article">
                                <input name="post_article" type="checkbox" id="post_article" value="1" 
                                <?php checked(true, get_option( 'post_article' )); ?>>博客发布博文 -> 社区发布帖子
                           </label>
                           </p>
                           <p>
                           <label for="update_article">
                                <input name="update_article" type="checkbox" id="update_article" value="1"
                                <?php checked(true, get_option( 'update_article' )); ?>>博客更新博文 -> 社区更新帖子
                           </label>
                           </p>
                           <p>
                           <label for="post_comment">
                                <input name="post_comment" type="checkbox" id="post_comment" value="1"
                                <?php checked(true, get_option( 'post_comment' )); ?>>博客发布评论 -> 社区发布回帖
                           </label>
                           </p>
                           <p>
                           <label for="sync_comment">
                                <input name="sync_comment" type="checkbox" id="sync_comment" value="1"
                                <?php checked(true, get_option( 'sync_comment' )); ?>>社区发布回帖 -> 博客发布评论
                           </label>
                           </p>
                        </fieldset>
                        <p class="description" id="sync_category-description">客户端接口：<?=get_bloginfo('url');?>/?hacpai-api=sync-comment</p>
                        <p class="description">请在这里设置查看你的同步接口（全部一致） <a href="https://hacpai.com/settings#soloPostURL" target="_blank">hacpai.com/settings</a></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <h2 class="title">同步记录</h2>
            <p>在这里可以查看最近十次的同步响应结果</p>
            <textarea name="log" id="log" style="resize:none" class="large-text code" rows="10" readonly="true">
                <?php
                    echo file_get_contents(dirname(__FILE__ ).DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'response.log');
                ?>
            </textarea>
            <?php submit_button();?>
        </form>
    </div>
    <?php
}
