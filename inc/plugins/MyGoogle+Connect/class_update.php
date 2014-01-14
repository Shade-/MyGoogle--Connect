<?php

/**
 * Upgrading routines
 */

class MyGoogle_Update
{
	
	private $version;
	
	private $old_version;
	
	private $plugins;
	
	private $info;
	
	public function __construct()
	{
		
		global $mybb, $db, $cache, $lang;
		
		if (!$lang->mygpconnect) {
			$lang->load("mygpconnect");
		}
		
		$this->load_version();
		
		$check = $this->check_update();
		
		if ($mybb->input['update'] == 'mygpconnect' and $check) {
			$this->update();
		}
		
	}
	
	private function load_version()
	{
		global $cache;
		
		$this->info        = mygpconnect_info();
		$this->plugins     = $cache->read('shade_plugins');
		$this->old_version = $this->plugins[$this->info['name']]['version'];
		$this->version     = $this->info['version'];
		
	}
	
	private function check_update()
	{
		global $lang, $mybb;
		
		if (version_compare($this->old_version, $this->version, "<")) {
			
			if ($mybb->input['update']) {
				return true;
			} else {
				flash_message($lang->mygpconnect_error_needtoupdate, "error");
			}
			
		}
		
		return false;
		
	}
	
	private function update()
	{
		global $db, $mybb, $cache, $lang;
		
		$new_settings = $drop_settings = array();
				
		// Get the gid
		$query = $db->simple_select("settinggroups", "gid", "name='mygpconnect'");
		$gid   = (int) $db->fetch_field($query, "gid");
		
		// 2.0
		if (version_compare($this->old_version, '2.0', "<")) {
			
			// Let's at least try to change that, anyway, 2.0 has backward compatibility so it doesn't matter if this fails
			require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
			find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('gplogin') . '#i', 'login');
			
		}
		
		if ($new_settings) {
			$db->insert_query_multiple('settings', $new_settings);
		}
		
		if ($drop_settings) {
			$db->delete_query('settings', "name IN ('mygpconnect_". implode("','mygpconnect_", $drop_settings) ."')");
		}
		
		rebuild_settings();
		
		// Update the current version number and redirect
		$this->plugins[$this->info['name']] = array(
			'title' => $this->info['name'],
			'version' => $this->version
		);
		
		$cache->update('shade_plugins', $this->plugins);
		
		flash_message($lang->sprintf($lang->mygpconnect_success_updated, $this->old_version, $this->version), "success");
		admin_redirect($_SERVER['HTTP_REFERER']);
		
	}
	
}

// Direct init on call
$GoogleConnectUpdate = new MyGoogle_Update();