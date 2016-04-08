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
    register_setting('hacpai-sync-wordpress-setting_options_group', 'sync_category');
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
                    <th scope="row"><label for="sync_category">选择同步 <?=$sync_category?></label></th>
                    <td>
                        <select name="sync_category" id="sync_category" class="postform">
                            <option class="level-0" value="2"<?=($sync_category == '2') ? ' selected="selected"' : ''?>>双向
                            </option>
                            <option class="level-0" value="1" <?=($sync_category == '1') ? ' selected="selected"' : ''?>>博客到黑客派
                            </option>
                            <option class="level-0" value="0"<?=($sync_category == '0') ? ' selected="selected"' : ''?>>关闭
                            </option>
                        </select>
                        <p class="description" id="sync_category-description">客户端接口：<?=get_bloginfo('url');?>/?hacpai-api=sync-comment</p>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button();?>
        </form>
    </div>
    <?php
}
