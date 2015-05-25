<?php
/**
 * SimpleThingFramework Cryptography & Hashing Class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.5.0
 * @see	http://tools.ietf.org/html/rfc3986
 */
/*
 * Change Log:
 * -----------
 * 2012-06-08	v0.5.0	Initial class creation with Matrix URI support
 *
 * ToDo:
 * -----
 * [ ] Implement all hashing and crypt options in a unifying UI.
 */
class STF_Crypt
{
	protected $alphabet = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	public function alphaRand($count, $alphabet=null)
	{
		if($alphabet === null)
			$alphabet = $this->alphabet;

		$alpha = '';
		for($i = 0; $i < $count; $i++)
			$alpha .= $alphabet[mt_rand(0, 63)];

		return $alpha;
	}

	public function blowfishHash($data, $cost=null, $alpha=null, $prefix=null)
	{
		if($alpha !== null)
		{
			$alpha = preg_filter('/[^.\/0-9a-z]/i', '', $alpha);
			if(strlen($alpha) > 22)
				$alpha = substr($alpha, 0, 22);
		}
		if($prefix !== null)
		{
			switch($prefix)
			{
				case '$2x$':
				case '$2y$':	break;
				case '$2a$':	$prefix = '$2x$';	break;
				case '$2$':
				default:		$prefix = null;		break;
			}
		}

		$salt = $this->blowfishSalt($cost, $alpha, $prefix);
		return crypt($data.$alpha, $salt['salt']);
	}

	public function blowfishHashVerify($data, $hash)
	{
		$test = crypt($data, $hash);
		return ($hash == $test) ? true : false;
	}

	/**
	 * Blowfish Salt Generator
	 *
	 * @param	$cost	The cost to generate. Default: random 04 - 31.
	 * @param	$alpha	The 22 character string to use. Default: random "./0-9A-Za-z".
	 * @param	$prefix	Either of $2$ (obsolete), $2a$ (bug found 2011), $2x$ ($2a$ with bug known) or $2y$ (with 2011 bugfix).
	 */
	public function blowfishSalt($cost=null, $alpha=null, $prefix='$2y$')
	{
		$result = array('salt'=>'', 'prefix'=>$prefix, 'cost'=>'', 'alpha'=>'');
		if($cost === null)
			$result['cost'] .= rand(4, 31);
		else
			$result['cost'] .= $cost;

		if(strlen($result['cost']) === 1)
			$result['cost'] = '0'.$result['cost'];

		if($prefix === null)
			$result['prefix'] = '$2y$';

		if($alpha === null)
			$result['alpha'] = $this->alphaRand(22);
		else
			$result['alpha'] = str_pad($alpha, 22, $alpha, STR_PAD_RIGHT);

		$result['salt'] = $result['prefix'].$result['cost'].'$'.$result['alpha'];

		return $result;
	}

	public function desExtendedSalt($count=1000, $alpha=null)
	{
		$result = array('salt'=>'', 'prefix'=>'_', 'count'=>'', 'alpha'=>'');
		$this->alphaRand();
	}

	public function md5PassHash($data, $pass, $count=1000, $offset=0)
	{
		global $codex, $util;

		if($offset > 11)
			$offset = 0;

		$passphrase = strlen($pass) < 12 ? str_pad($pass, 12, $pass) : $pass;
		$key = substr($pass, $offset);
		if($offset !== 0)
			$key .= substr($passphrase, 0, $offset);

		// echo 'key: '.$key."\n";

		if($count === 0)
		{
			$int = (string) mt_rand(1, 4000);
			$div = (string) mt_rand(0, 31);
			$dec = mt_rand(0, 6);
			// return 'bcdiv('.$int.', '.$div.', '.$dec.') = '.$util->loop($codex->bcdiv($int, $div, $dec));
			return $int.' '.$codex->base64url_encode($codex->convBase($int, $codex::BASE_10, $codex::BASE_32));//'$md5$'.$pass.'$'.md5($data.$key);
		}
		else
		{
			return $this->md5PassHash(md5($data.$key), $passphrase, --$count, ++$offset);
		}
	}

	public function md5HmacHash($data, $pass, $count=1, $offset=0)
	{
		$passphrase = strlen($pass) < 12 ? str_pad($pass, 12, $pass) : $pass;

		if($offset > (strlen($passphrase) - 1))
			$offset = 0;

		$key = substr($pass, $offset);
		if($offset !== 0)
			$key .= substr($passphrase, 0, $offset);

		if($count === 0)
			return hash_hmac('md5', $data, $key);
		else
		{
			return $this->md5HmacHash(hash_hmac('md5', $data, $key), $passphrase, --$count, ++$offset);
		}
	}
}
?>