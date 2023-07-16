<div class="wrap">
    <div class="notice notice-error is-dismissible"><p><?=$error?></p></div>
    <h1>Odds Widget Settings</h1>

    <form method="post" action="<?=esc_html(admin_url('admin-post.php'))?>" novalidate="novalidate">
        <input type="hidden" name="action" value="odds_widget_save_settings">
        <?php wp_nonce_field('odds_widget_save_settings', $nonce_key, true)?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="odds_format"><?=esc_html__('API key', 'odds-widget')?></label></th>
                    <td>
                        <div>
                            <input name="api_key" type="text" placeholder="<?=esc_html__('Enter the-odds-api key', 'odds-widget')?>" value="<?=$api_key?>">
                            <button name="save_key" value="1" class="button button-primary"><?=esc_html__('Save key', 'odds-widget')?></button>
                        </div>
                        <?php if(!empty($quota_left)) { ?><p class="description"><?=$quota_left?></p><?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>

</div>