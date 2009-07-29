<div class="wrap">
  <h2>Extendy Configuration</h2>
  <div class="narrow">
    <p>
      The Extendy plugin quickly installs the Extendy toolbar to your blog.
    </p>
    <?php if($extendy_campaign_id && $extendy_campaign_name): ?>
      <div id="extendy-current-settings">
        Currently installed campaign: <?php echo $extendy_campaign_name; ?>
        <form action="" method="post" id="extendy-disable" style="display:inline;">
        <?php extendy_nonce_field($extendy_nonce); ?>
        <input type="submit" class="button-secondary" value="Uninstall" name="extendy_uninstall"  />
        </form>
      </div>
    <?php endif ;?>
    
    <form action="" method="post" id="extendy-conf" style="">
      <?php extendy_nonce_field($extendy_nonce); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Campaign API Key</th>
          <td><input name="extendy_campaign_api_key" id="extendy_campaign_api_key" type="text" size="50" maxlength="50" value="<?php echo $extendy_campaign_api_key; ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Disabled?</th>
          <td>
          <input type="hidden" name="extendy_campaign_disabled" value="0" />
          <input name="extendy_campaign_disabled" id="extendy_campaign_disabled" type="checkbox" value="1" <?php echo ($extendy_campaign_disabled ? "checked='checked'" : '') ?>>
            
          </td>
        </tr>
      </table>
      <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
  </div>
</div>
