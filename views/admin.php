<div class="wrap">
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

        <p><?=esc_html__('This settings are defaults. You can additionally customize widget in editor.', 'odds-widget')?></p>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="odds_format"><?=esc_html__('Odds format', 'odds-widget')?></label></th>
                    <td>
                        <select name="odds_format" id="odds_format" <?=empty($api_key) ? 'disabled' : ''?>>
                            <?php foreach ($odds_formats as $odds_format) { ?>
                                <option <?=($current_odds_format ?? null) === $odds_format ? 'selected' : ''?> value="<?=$odds_format?>"><?=esc_html__(ucfirst($odds_format), 'odds-widget')?></option>
                            <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="group"><?=esc_html__('Group', 'odds-widget')?></label></th>
                    <td>
                        <select name="group" id="group" <?=empty($api_key) ? 'disabled' : ''?>>
                            <?=empty($current_sport['group']) ? '<option id="empty_group_option"></option>' : ''?>
                            <?php foreach ($all_groups as $a_group) { ?>
                                <option <?=($current_sport['group'] ?? null) === $a_group ? 'selected' : ''?>><?=$a_group?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sport_key"><?=esc_html__('Sport', 'odds-widget')?></label></th>
                    <td>
                        <select name="sport_key" id="sport_key" aria-describedby="sport_key_description" <?=empty($sports_of_group) || empty($api_key) ? 'disabled' : ''?>>
                            <?php foreach (array_filter($sports_of_group) as $sport) { ?>
                                <option <?=($current_sport['key'] ?? null) === $sport['key'] ? 'selected' : ''?> value="<?=$sport['key']?>"><?=$sport['title']?></option>
                            <?php } ?>
                        </select>
                        <p class="description" id="sport_key_description"><?=($current_sport['description'] ?? null)?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="game_id"><?=esc_html__('Game', 'odds-widget')?></label></th>
                    <td>
                        <select name="game_id" id="game_id" <?=empty($current_game_id) || empty($api_key) ? 'disabled' : ''?>>
                            <?php foreach ($all_odds as $a_odd) { ?>
                                <option <?=$current_game_id === $a_odd['id'] ? 'selected' : ''?> value="<?=$a_odd['id']?>">
                                    <?=sprintf("%s: %s - %s", explode('T', $a_odd['commence_time'])[0], $a_odd['home_team'], $a_odd['away_team'])?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?=esc_html__('Bookmakers', 'odds-widget')?></th>
                    <td>
                        <fieldset>
                            <table class="bookmakers-table">
                                <tbody id="bookmakers-table-body">
                                <?php foreach ($all_odds[$current_game_id]['bookmakers'] as $bookmaker) { ?>
                                    <tr>
                                        <td><input <?=empty($api_key) ? 'disabled' : ''?>
                                                type="checkbox"
                                                name="bookmakers[<?=$bookmaker['key']?>]"
                                                id="bookmakers[<?=$bookmaker['key']?>]"
                                                value="1"
                                                <?=in_array($bookmaker['key'], $current_bookmakers) ? 'checked="checked"' : ''?>
                                        ></td>
                                        <td><label for="bookmakers[<?=$bookmaker['key']?>]"><?=$bookmaker['title']?></label></td>
                                        <td><input <?=empty($api_key) ? 'disabled' : ''?>
                                                type="url"
                                                id="bookmakers_url[<?=$bookmaker['key']?>]"
                                                name="bookmakers_url[<?=$bookmaker['key']?>]"
                                                value="<?=$bookmakers_url_settings[$bookmaker['key']] ?? ''?>"
                                                placeholder="<?=esc_html__('Set partner link', 'odds-widget')?>"
                                                autocomplete="off"
                                        ></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit"><input type="submit" <?=empty($api_key) ? 'disabled' : ''?> name="submit" id="submit" class="button button-primary" value="<?=esc_html__('Save Changes', 'odds-widget')?>"></p></form>

</div>