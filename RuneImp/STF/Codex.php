<?php
/**
 * SimpleThingFramework Codex
 *
 * RFC 4648 complient.
 *
 * @see http://tools.ietf.org/html/rfc4648
 */
/**
 * Cryptography & Hashing Class
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
class STF_Codex
{
	// CLASS CONSTANTS //
	const BASE_8 = '01234567';// Octal
	const BASE_10 = '0123456789';// Decimal
	const BASE_16 = '0123456789ABCDEF';// Hex
	const BASE_26_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const BASE_32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	const BASE_32_HEX = '0123456789ABCDEFGHIJKLMNOPQRSTUV';
	const BASE_32_PAD = '=';
	const BASE_64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	const BASE_64_PAD = '=';
	const BASE_52_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	const BASE_62_ALPHANUM = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	const BASE_64_HASH = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	const BASE_64_URL = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

	// CLASS PROPERTIES //
	private $alpha_lower;
	private $alpha_upper;
	private $props;

	public function __construct()
	{
		// Setup Properties RWI //
		$this->props = array();
		$this->props['alpha_lower'] = 5;// 1 = read, 2 = write, 4 = isset
		$this->props['alpha_upper'] = 5;

		$this->alpha_lower = array_map('strtolower', str_split(self::BASE_26_ALPHA));
		$this->alpha_upper = str_split(self::BASE_26_ALPHA);
	}

	/**
	 * Dynamic Getter
	 *
	 * Uses bitwise check against the $props array to determine read status.
	 *
	 * @param	$prop	Property name to get.
	 * @return	Value for the property if it exists and is readable.
	 */
	public function __get($prop)
	{
		// echo '<pre>'.__METHOD__.'() $this->'.$prop.': '.print_r($this->$prop, true)."</pre>\n";
		if(array_key_exists($prop, $this->props) && (($this->props[$prop] & 1) == 1))
			return $this->$prop;
	}

	public function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public function base64url_decode($data)
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	/**
	 * Binary Calculator Math Modulus substitute
	 *
	 * @see http://us.php.net/manual/en/function.bcmod.php#38474
	 */
	public function bcmod($x, $y)
	{
		if($y == 0)
			return null;

		// how many numbers to take at once? carefull not to exceed (int)
		$take = 5;
		$mod = '';

		do
		{
			$a = (int)$mod.substr($x, 0, $take);
			$x = substr($x, $take);
			$mod = $a % $y;
		}
		while(strlen($x));

		return (int)$mod;
	}

	/**
	 * Binary Calculator Math Division substitute
	 *
	 * @see http://us.php.net/manual/en/function.bcdiv.php#84255
	 */
	public function bcdiv($first, $second, $scale = 0)
	{
		if($second == 0)
			return null;

		$result = (string) $first / $second;
		$decimal = strrpos($result, '.');
		if($decimal !== false)
		{
			$int = substr($result, 0, $decimal);
			if($scale > 0)
			{
				$decimal = substr($result, $decimal+1);
				$length = strlen($decimal);
				if($length < $scale)
					$decimal = str_pad($decimal, $scale, '0', STR_PAD_RIGHT);
				else if($length > $scale)
					$decimal = substr($decimal, 0, $scale);
				$result = $int.'.'.$decimal;
			}
			else
				$result = $int;
		}

		return $result;
	}

	/**
	 * Binary Calculator Math Pow substitute
	 *
	 * @see http://us.php.net/manual/en/function.bcpow.php#49913
	 */
	public function bcpow($num, $power, $scale=3)
	{
		$awnser = '1';
		while($power)
		{
			$awnser = bcmul($awnser, $num, 100);
			$power = bcsub($power, '1');
		}
		return rtrim($awnser, '0.');
	}

	/**
	 * Numeric base conversion method
	 *
	 * @see http://us.php.net/manual/en/function.base-convert.php#106546
	 */
	public function convBase($numberInput, $fromBaseInput, $toBaseInput)
	{
		if($fromBaseInput == $toBaseInput)
			return $numberInput;

		$fromBase = str_split($fromBaseInput, 1);
		$toBase = str_split($toBaseInput, 1);
		$number = str_split($numberInput, 1);
		$fromLen = strlen($fromBaseInput);
		$toLen = strlen($toBaseInput);
		$numberLen = strlen($numberInput);
		$retval='';

		if($toBaseInput == '0123456789')
		{
			$retval = 0;
			for($i = 1; $i <= $numberLen; $i++)
			{
				$retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase), bcpow($fromLen, $numberLen - $i)));
			}
			return $retval;
		}
		if($fromBaseInput != '0123456789')
			$base10 = $this->convBase($numberInput, $fromBaseInput, '0123456789');
		else
			$base10 = $numberInput;

		if($base10 < strlen($toBaseInput))
			return $toBase[$base10];

		while($base10 != '0')
		{
			if(function_exists('bcmod'))
				$retval = $toBase[bcmod($base10, $toLen)].$retval;
			else
				$retval = $toBase[$this->bcmod($base10, $toLen)].$retval;

			if(function_exists('bcdiv'))
				$base10 = bcdiv($base10, $toLen, 0);
			else
				$retval = $this->bcdiv($base10, $toLen, 0);
			
		}
		return $retval;
	}

	public function intToAlphaBaseN($n, $baseArray)
	{
		$l=count($baseArray);
		$s = '';
		for ($i = 1; $n >= 0 && $i < 10; $i++) {
			$s =  $baseArray[($n % pow($l, $i) / pow($l, $i - 1))].$s;
			$n -= pow($l, $i);
		}
		return $s;
	}
}
?>