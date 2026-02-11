<?php


access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">
<form action="<?php echo plugin_page( 'config' ) ?>" method="post">
<fieldset>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
    <h4 class="widget-title lighter">
        <i class="ace-icon fa fa-exchange"></i>
        <?php echo plugin_lang_get( 'title' ) ?>
    </h4>
</div>

<?php echo form_security_field( 'plugin_MWCCDCChat_config' ) ?>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

    <tr>
      <td class="category">
        The domain of the chat server (ex. chat.ccdc.events)
      </td>
      <td colspan="2">
        <input type="text" name="chat_domain" value="<?php echo plugin_config_get( 'chat_domain' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        The e-mail address of the Zulip bot
      </td>
      <td colspan="2">
        <input type="text" name="bot_email" value="<?php echo plugin_config_get( 'bot_email' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        The API token of the Zulip bot
      </td>
      <td colspan="2">
        <input type="text" name="bot_token" value="<?php echo plugin_config_get( 'bot_token' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Format of the team channel names<br>
        <span class="small">{project_name} will be replaced with the name of the project the ticket was filed under<span>
      </td>
      <td colspan="2">
        <input type="text" name="team_channel_format" value="<?php echo plugin_config_get( 'team_channel_format' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Confirmation regex of team channel names<br>
        <span class="small">This is used to confirm that we're about to send to an appropriate channel<span>
      </td>
      <td colspan="2">
        <input type="text" name="team_channel_regex" value="<?php echo plugin_config_get( 'team_channel_regex' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Format of the channel topic to send a message to
        <span class="small">{bug_id} will be replaced with the ticket number<span>
      </td>
      <td colspan="2">
        <input type="text" name="team_topic_format" value="<?php echo plugin_config_get( 'team_topic_format' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Format of the team group names<br>
        <span class="small">{project_name} will be replaced with the name of the project the ticket was filed under<span>
      </td>
      <td colspan="2">
        <input type="text" name="team_group_format" value="<?php echo plugin_config_get( 'team_group_format' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Confirmation regex of team group names<br>
        <span class="small">This is used to confirm that we're about to notify an appropriate group<span>
      </td>
      <td colspan="2">
        <input type="text" name="team_group_regex" value="<?php echo plugin_config_get( 'team_group_regex' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Green team group to @ when a new ticket is created
      </td>
      <td colspan="2">
        <input type="text" name="green_team_chat_group" value="<?php echo plugin_config_get( 'green_team_chat_group' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Parent project for operations only tickets
      </td>
      <td colspan="2">
        <input type="text" name="operations_project" value="<?php echo plugin_config_get( 'operations_project' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Zulip operations group to @
      </td>
      <td colspan="2">
        <input type="text" name="operations_group" value="<?php echo plugin_config_get( 'operations_group' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Zulip operations channel for operations only tickets
      </td>
      <td colspan="2">
        <input type="text" name="operations_channel" value="<?php echo plugin_config_get( 'operations_channel' )?>" />
      </td>
    </tr>

    <tr>
      <td class="category">
        Events to notify for
      </td>
      <td colspan="2">
        <input type="checkbox" name="notification_bug_report" <?php if (plugin_config_get( 'notification_bug_report' )) echo "checked"; ?> /> Send notification on new ticket <br>
        <input type="checkbox" name="notification_bug_update" <?php if (plugin_config_get( 'notification_bug_update' )) echo "checked"; ?> /> Send notification on ticket update <br>
        <input type="checkbox" name="notification_bugnote_add" <?php if (plugin_config_get( 'notification_bugnote_add' )) echo "checked"; ?> /> Send notification when note added to ticket <br>
      </td>
    </tr>


</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
    <input type="submit" class="btn btn-primary btn-white btn-round" value="Update" />
</div>
</div>
</div>
</fieldset>
</form>
</div>
</div>

<?php
layout_page_end();
