<?php

form_security_validate( 'plugin_MWCCDCChat_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

/**
 * Sets plugin config option if value is different from current/default
 * @param string $p_name  option name
 * @param string $p_value value to set
 * @return void
 */
function config_set_if_needed( $p_name, $p_value ) {
	if ( $p_value != plugin_config_get( $p_name ) ) {
		plugin_config_set( $p_name, $p_value );
	}
}

$t_redirect_url = plugin_page( 'config_page', true );
layout_page_header( null, $t_redirect_url );
layout_page_begin();


config_set_if_needed( 'chat_domain' , gpc_get_string( 'chat_domain' ) );
config_set_if_needed( 'bot_email' , gpc_get_string( 'bot_email' ) );
config_set_if_needed( 'bot_token' , gpc_get_string( 'bot_token' ) );
config_set_if_needed( 'team_channel_format' , gpc_get_bool( 'team_channel_format' ) );
config_set_if_needed( 'team_channel_regex' , gpc_get_bool( 'team_channel_regex' ) );
config_set_if_needed( 'team_topic_format' , gpc_get_bool( 'team_topic_format' ) );
config_set_if_needed( 'team_group_format' , gpc_get_string( 'team_group_format' ) );
config_set_if_needed( 'team_group_regex' , gpc_get_string( 'team_group_regex' ) );
config_set_if_needed( 'green_team_chat_group' , gpc_get_string( 'green_team_chat_group' ) );
config_set_if_needed( 'notification_bug_report' , gpc_get_bool( 'notification_bug_report' ) );
config_set_if_needed( 'notification_bug_update' , gpc_get_bool( 'notification_bug_update' ) );
config_set_if_needed( 'notification_bugnote_add' , gpc_get_bool( 'notification_bugnote_add' ) );

form_security_purge( 'plugin_MWCCDCChat_config' );

html_operation_successful( $t_redirect_url );
layout_page_end();
