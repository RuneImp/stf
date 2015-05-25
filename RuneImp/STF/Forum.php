<?php
/**
 * STF Logger Class
 *
 * Requires PHP 5.3.0+
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2012-10-15	v0.1.0	Initial Class Creation
 *
 * ToDo:
 * -----
 * [ ] Manage a manifest file to list current forum topics.
 * [ ] Make sure users are logged in to make comments.
 * [ ] Config should define if topics are publicly viewable.
 * [ ] Create Topic class for managing individual topics
 * [ ] Topics should have their own config files which override the forum config.
 * [ ] Topic titles should be slugified and the topic config and it's responses kept within the topics slugified folder.
 * [ ] A Paging manager will handle topic paging of responses
 * [*] Create Avatar manager
 */
class STF_Forum extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //
	public $avatar_image_size = 80;
	public $force_secure_links = null;// TRUE to force secure, FALSE to force unsecure or NULL to autodetect
	public $gravatar_image_default = null;// default (Gravatar logo), URL to a public image, 404, mm, identicon, monsterid, wavatar, retro, blank
	public $gravatar_qrcode_image_size = 80;

	// CLASS VARIABLES //
	protected $config;
	protected $log;// STF_Logger instance
	protected $topic_slug;
	protected $uri;// STF_Net_URI instance

	public function __construct($uri)
	{
		$this->props = array();

		$this->uri = $uri;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_DEBUG);
		$this->log->file_output = true;
		$this->log->debug('['.__FUNCTION__.']');
	}

	public function init($config_file)
	{
		$this->log->debug('['.__FUNCTION__.']', '$this->uri->path_slug:', $this->uri->path_slug);

		$this->loadConfig($config_file);
	}

	protected function loadConfig($config_file)
	{
		$this->log->debug('['.__FUNCTION__.']', '$config_file:', $config_file);

		$this->config = file_get_contents($config_file);
		$this->config = json_decode($this->config, true);
		$this->log->debug('['.__FUNCTION__.']', '$this->config:', $this->config);

		$comparison = substr_compare($this->config['base_path'], $this->uri->path_slug, 0);
		$this->log->debug('['.__FUNCTION__.']', '$comparison:', $comparison, substr($this->uri->path_slug, $comparison));
	}
}

?>