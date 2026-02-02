<?php
/**
 * MWCCDC Chat
 * Copyright (C) Jimmy Murphy (giga1699@gmail.com)
 * 
 * Based on MantisBT-Slack (https://github.com/infojunkie/MantisBT-Slack)
 *
 * MWCCDC Chat is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * MWCCDC Chat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MWCCDC Chat; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 * or see http://www.gnu.org/licenses/.
 */

class MWCCDCChatPlugin extends MantisPlugin {
    var $skip = false;

    function register() {
        $this->name = "MWCCDCChat";
        $this->description = "Integrates with the MWCCDC chat system to provide notification of ticket events.";
        $this->page = 'config_page';
        $this->version = '0.0.1';
        $this->requires = array(
            'MantisCore' => '2.0.0',
        );
        $this->author = 'Jimmy Murphy';
        $this->contact = 'giga1699@gmail.com';
        $this->url = 'https://caeepnc.org/mwccdc/';
    }

    function install() {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            plugin_error('ERROR_PHP_VERSION');
            return false;
        }
        if (!extension_loaded('curl')) {
            plugin_error('ERROR_NO_CURL');
            return false;
        }
        return true;
    }

    function config() {
        return array(
            'chat_domain' => 'chat.ccdc.events',
            'bot_email' => 'support-bot@comp.ccdc.events',
            'bot_token' => '',
            'team_channel_format' => "{project_name} Support",
            'team_channel_regex' => "/^Team \d+ Support/",
            'team_topic_format' => "Ticket {bug_id}",
            'team_group_format' => "{project_name}",
            'team_group_regex' => "/^Team \d+/",
            'green_team_chat_group' => 'Green Team',
            'notification_bug_report' => true,
            'notification_bug_update' => true,
            'notification_bugnote_add' => true,
        );
    }

    function hooks() {
        return array(
            'EVENT_REPORT_BUG' => 'bug_report',
            'EVENT_UPDATE_BUG' => 'bug_update',
            'EVENT_BUG_ACTION' => 'bug_action',
            'EVENT_BUGNOTE_ADD' => 'bugnote_add',
        );
    }


    function skip_event($event) {
        $configs = array(
            'EVENT_REPORT_BUG' => 'notification_bug_report',
            'EVENT_UPDATE_BUG' => 'notification_bug_update',
            'EVENT_BUGNOTE_ADD' => 'notification_bugnote_add',
        );
        if (!array_key_exists($event, $configs)) return true;
        return !plugin_config_get($configs[$event]);
    }

    function bug_report($event, $bug, $bug_id) {
        $this->skip = $this->skip ||
            $this->skip_event($event);
    
        $project = project_get_name($bug->project_id);
        $url = string_get_bug_view_url_with_fqdn($bug_id);

        $greenGroup = plugin_config_get('green_team_chat_group');
        $teamGroup = preg_replace("/{project_name}/", $project, plugin_config_get('team_group_format'));
        $channel = preg_replace("/{project_name}/", $project, plugin_config_get('team_channel_format'));
        $channelTopic = preg_replace("/{bug_id}/", $bug_id, plugin_config_get('team_topic_format'));

        if (preg_match(plugin_config_get('team_channel_regex'), $channel) !== 1) return;
        if (preg_match(plugin_config_get('team_group_regex'), $teamGroup) !== 1) return;

        $msg = sprintf("@*%s* @*%s* A new support ticket has been opened!\r\nLink to ticket: %s", $greenGroup, $teamGroup, $url);

        $this->notify($msg, $channel, $channelTopic);
    }

    function bug_update($event, $bug_existing, $bug_updated) {
        $this->skip = $this->skip ||
            $this->skip_event($event);
    
        $project = project_get_name($bug_updated->project_id);

        $channel = preg_replace("/{project_name}/", $project, plugin_config_get('team_channel_format'));
        $channelTopic = preg_replace("/{bug_id}/", $bug_updated->id, plugin_config_get('team_topic_format'));

        if (preg_match(plugin_config_get('team_channel_regex'), $channel) !== 1) return;

        $msg = sprintf("Support ticket has been updated.\r\nTicket status: **%s**", MantisEnum::getLabel( lang_get( 'status_enum_string' ), $bug_updated->status));

        $this->notify($msg, $channel, $channelTopic);
    }

    function bug_action($event, $action, $bug_id) {
        $this->skip = $this->skip;

        if ($action !== 'DELETE') {
            $bug = bug_get($bug_id);
            $this->bug_report_update('EVENT_UPDATE_BUG', $bug, $bug_id);
        }
    }

    function bugnote_add($event, $bug_id, $bugnote_id, $files = null) {
        $bug = bug_get($bug_id);
        $bugnote = bugnote_get($bugnote_id);

        $this->skip = $this->skip ||
            $this->skip_event($event);

        $url = string_get_bugnote_view_url_with_fqdn($bug_id, $bugnote_id);
        $project = project_get_name($bug->project_id);
        $note = bugnote_get_text($bugnote_id);
        $msg = sprintf("A new note has been added to this ticket!\r\nNote text:\r\n```\r\n%s\r\n```\r\nLink to note: %s", $note, $url);

        $channel = preg_replace("/{project_name}/", $project, plugin_config_get('team_channel_format'));
        $channelTopic = preg_replace("/{bug_id}/", $bug_id, plugin_config_get('team_topic_format'));

        if (preg_match(plugin_config_get('team_channel_regex'), $channel) !== 1) return;

        $this->notify($msg, $channel, $channelTopic);
    }

    function notify($msg, $channel, $channelTopic) {
        if ($this->skip) return;
        if (empty($channel)) return;
        if (empty($channelTopic)) return;

        $ch = curl_init();
        $url = sprintf('https://%s/api/v1/messages', plugin_config_get('chat_domain'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', plugin_config_get('bot_email'), plugin_config_get('bot_token')));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $payload = array(
            'type'=> 'stream',
            'to' => $channel,
            'topic' => $channelTopic,
            'content' => $msg,
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);        
        if (json_decode($result)->{'result'} != 'success') {
            print($result);
            trigger_error(curl_errno($ch) . ': ' . curl_error($ch), E_USER_WARNING);
            plugin_error('ERROR_CURL', E_USER_ERROR);
        }
        curl_close($ch);
    }

}
