<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Check permission and settings for bbcode, img, url, etc
 */
class post_options
{
	// the permissions, so I can change them later easier if need be for a different mod or whatever...
	var $auth_bbcode = false;
	var $auth_smilies = false;
	var $auth_img = false;
	var $auth_url = false;
	var $auth_flash = false;

	// whether these are allowed or not
	var $bbcode_status = false;
	var $smilies_status = false;
	var $img_status = false;
	var $url_status = false;
	var $flash_status = false;

	// whether or not they are enabled in the post
	var $enable_bbcode = false;
	var $enable_smilies = false;
	var $enable_magic_url = false;

	/**
	 * Automatically sets the defaults for the $auth_ vars
	 */
	function post_options()
	{
		global $auth, $user_founder, $blog_plugins;

		$this->auth_bbcode = ($auth->acl_get('u_blogbbcode') || $user_founder) ? true : false;
		$this->auth_smilies = ($auth->acl_get('u_blogsmilies') || $user_founder) ? true : false;
		$this->auth_img = ($auth->acl_get('u_blogimg') || $user_founder) ? true : false;
		$this->auth_url = ($auth->acl_get('u_blogurl') || $user_founder) ? true : false;
		$this->auth_flash = ($auth->acl_get('u_blogflash') || $user_founder) ? true : false;

		$blog_plugins->plugin_do('post_options');
	}

	/**
	 * set the status to the  variables above, the enabled options are if they are enabled in the posts(by who ever is posting it)
	 */
	function set_status($bbcode, $smilies, $url)
	{
		global $config, $auth, $user_founder, $blog_plugins;

		$this->bbcode_status = (($config['allow_bbcode'] && $this->auth_bbcode) || $user_founder) ? true : false;
		$this->smilies_status = (($config['allow_smilies'] && $this->auth_smilies) || $user_founder) ? true : false;
		$this->img_status = (($this->auth_img && $this->bbcode_status) || $user_founder) ? true : false;
		$this->url_status = (($config['allow_post_links'] && $this->auth_url && $this->bbcode_status) || $user_founder) ? true : false;
		$this->flash_status = (($this->auth_flash && $this->bbcode_status) || $user_founder) ? true : false;

		$this->enable_bbcode = ($this->bbcode_status && $bbcode) ? true : false;
		$this->enable_smilies = ($this->smilies_status && $smilies) ? true : false;
		$this->enable_magic_url = ($this->url_status && $url) ? true : false;

		$blog_plugins->plugin_do('post_options_set_status');
	}

	/**
	 * Set the options in the template
	 */
	function set_in_template()
	{
		global $template, $user, $phpbb_root_path, $phpEx, $blog_plugins;

		// Assign some variables to the template parser
		$template->assign_vars(array(
			// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
			'S_BBCODE_CHECKED'			=> ($this->enable_bbcode) ? '' : ' checked="checked"',
			'S_SMILIES_CHECKED'			=> ($this->enable_smilies) ? '' : ' checked="checked"',
			'S_MAGIC_URL_CHECKED'		=> ($this->enable_magic_url) ? '' : ' checked="checked"',

			// To show the Options: section on the bottom left
			'BBCODE_STATUS'				=> ($this->bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'				=> ($this->img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'				=> ($this->flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'			=> ($this->smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'				=> ($this->url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

			// To show the option to turn each off while posting
			'S_BBCODE_ALLOWED'			=> $this->bbcode_status,
			'S_SMILIES_ALLOWED'			=> $this->smilies_status,
			'S_LINKS_ALLOWED'			=> $this->url_status,

			// To show the BBCode buttons for each on top
			'S_BBCODE_IMG'				=> $this->img_status,
			'S_BBCODE_URL'				=> $this->url_status,
			'S_BBCODE_FLASH'			=> $this->flash_status,
		));

		$blog_plugins->plugin_do('post_options_set_in_template');
	}
}
?>