<?php
/**
 * STF Forum Avatar Class
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
 * [ ] ...
 */
class STF_Forum_Avatar extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //
	public $avatar_image_size = 80;
	public $gravatar_image_default = null;// default (Gravatar logo), URL to a public image, 404, mm, identicon, monsterid, wavatar, retro, blank
	public $gravatar_qrcode_image_size = 80;

	// CLASS VARIABLES //
	protected $forum;// STF_Forum instance
	protected $log;// STF_Logger instance

	public function __construct($forum)
	{
		$this->forum = $forum;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_DEBUG);
		$this->log->file_output = true;
		$this->log->debug('['.__FUNCTION__.']');
	}

	public function gravitarHash($email)
	{
		$this->log->debug('['.__FUNCTION__.']', '$email:', $email);
		return md5(strtolower(trim($email)));
	}

	public function gravitarImageLink($email, $size=null, $default_image=null, $force_default=false, $rating='g')
	{
		$this->log->debug('['.__FUNCTION__.']', '$email:', $email);

		if($this->forum->force_secure_links === true || ($this->forum->force_secure_links !== false && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))
			$url = 'https://secure.gravatar.com';
		else
			$url = 'http://www.gravatar.com';
		$url .= '/avatar/'.$this->gravitarHash($email);

		$params = array();

		// Set Image Size //
		if(!empty($size) && is_numeric($size) && ($size >= 1 && $size <= 2048))
			$params['s'] = intval($size);
		else if(!empty($this->avatar_image_size) && is_numeric($this->avatar_image_size) && ($this->avatar_image_size >= 1 && $this->avatar_image_size <= 2048))
			$params['s'] = intval($this->avatar_image_size);

		// A Default Image //
		if(!empty($default_image))
			$params['d'] = $default_image;
		else if(!empty($this->gravatar_image_default))
			$params['d'] = $this->gravatar_image_default;

		// Force Default Image //
		if($force_default)
			$params['f'] = 'y';

		// Check Rating //
		if(!empty($rating))
		{
			$rating = strtolower($rating);
			if($rating == 'pg' || $rating == 'r' || $rating == 'x')
				$params['r'] = $rating;
		}

		// Add Query Parameters //
		if(count($params) > 0)
			$url .= '?'.http_build_query($params);

		return $url;
	}

	public function gravitarProfileLink($email, $type='hcard', $size=null)
	{
		$this->log->debug('['.__FUNCTION__.']', '$email:', $email);

		if($this->forum->force_secure_links === true || ($this->forum->force_secure_links !== false && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))
			$url = 'https://secure.gravatar.com';
		else
			$url = 'http://www.gravatar.com';
		$url .= '/'.$this->gravitarHash($email);
		
		$params = array();

		$type = strtolower($type);

		switch($type)
		{
			case 'qr':
				// QR Code //
				if($type == 'qr' && !empty($size) && is_numeric($size) && ($size >= 1 && $size <= 2048))
					$params['s'] = intval($size);
				else if(!empty($this->gravatar_qrcode_image_size) && is_numeric($this->gravatar_qrcode_image_size) && ($this->gravatar_qrcode_image_size >= 1 && $this->gravatar_qrcode_image_size <= 2048))
					$params['s'] = intval($this->gravatar_qrcode_image_size);
				break;
			case 'hcard':
			default:
				// Default hCard HTML //
		}

		// Set Image Size //
		

		// Add Query Parameters //
		$this->log->debug('['.__FUNCTION__.']', 'count($params):', count($params));
		if(count($params) > 0)
			$url .= '?'.http_build_query($params);

		return $url;
	}
}

?>