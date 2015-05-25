<?php
/**
 * STF YAML API Class
 *
 * Will use in order of priority if found:
 *	php-yaml (PHP Extension)
 *	syck (PHP Extension)
 *	sfYaml (Symphony YAML Class)
 *	Spyc (Original Symphony YAML Class)
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 * @see http://www.php.net/manual/en/ref.yaml.php
 * @see https://github.com/indeyets/syck
 * @see https://github.com/symfony/Yaml
 * @see https://github.com/mustangostang/spyc
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 *	2012-09-09	v1.0.0		Class Creation. PEAR style based on com\simplethingframework\util\YAML v2.0.0
 */

namespace RuneImp\STF\UTIL;

class YAML
{
	private $implimentations;
	private $yamlDecode = null;
	private $yamlEncode = null;
	
	public function __construct()
	{
		$this->implimentations = (object) array();
		$this->implimentations->decoders = array();
		$this->implimentations->encoders = array();

		// PECL PHP-YAML //
		if(function_exists('yaml_parse'))
		{
			// Parses a YAML stream to PHP Data
			$this->implimentations->decoders[] = 'yaml_parse';
			// yaml_parse($input, $position=0, &$numberOfDocs, callbacks);// callbacks is a funcion or array of YAML tag => callback mappings
			// $position -1 returns an array of documents
					
			$this->yamlDecode = array('RuneImp\STF\UTIL\YAML', 'phpYamlDecode');
		}
		if(function_exists('yaml_parse_file'))
		{
			// Parses a YAML stream to PHP Data
			$this->implimentations->decoders[] = 'yaml_parse_file';
			// yaml_parse_file($path, $position=0, &$numberOfDocs, callbacks);
			// $position -1 returns an array of documents
		}
		if(function_exists('yaml_parse_url'))
		{
			// Parses a YAML stream to PHP Data
			$this->implimentations->decoders[] = 'yaml_parse_url';
			// yaml_parse_url($url, $position=0, &$numberOfDocs, callbacks);
			// $position -1 returns an array of documents
		}
		if(function_exists('yaml_emit'))
		{
			// Converts PHP data to YAML
			$this->implimentations->encoders[] = 'yaml_emit';
			// yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);// Defaults to YAML_ANY_ENCODING, YAML_ANY_BREAK
			
			$this->yamlEncode = $this->phpYamlEncode;
		}
		if(function_exists('yaml_emit_file'))
		{
			// Converts PHP data to YAML
			$this->implimentations->encoders[] = 'yaml_emit_file';
			// yaml_emit_file($path, $data, YAML_UTF8_ENCODING, YAML_LN_BREAK);// Defaults to YAML_ANY_ENCODING, YAML_ANY_BREAK
		}
		
		// PECL Syck Library //
		if(function_exists('syck_load'))
		{
			// Parses a YAML stream
			$this->implimentations->decoders[] = 'syck_load';
			
			if($this->yamlDecode === null)
				$this->yamlDecode = $this->syckDecode;
		}
		if(function_exists('syck_dump'))
		{
			// Converts arrays to YAML
			$this->implimentations->encoders[] = 'syck_dump';
			
			if($this->yamlEncode === null)
				$this->yamlEncode = $this->syckEncode;
		}
		
		/**
		 * If no binary options found check for PHP libraries
		 */
		if(count($this->implimentations->decoders) == 0 || count($this->implimentations->encoders) == 0)
		{
			// Test for Symfony Yaml //
			$found_symfony_yaml = class_exists('Symfony\Component\Yaml\Yaml');
			// echo '<pre>'.__CLASS__.' $found_symfony_yaml: '.var_export($found_symfony_yaml, true)."</pre>\n";
			
			if( $found_symfony_yaml )
			{
				// use Symfony\Component\Yaml\Yaml;
				$this->implimentations->decoders[] = 'Symfony\Component\Yaml\Yaml::parse';
				$this->implimentations->encoders[] = 'Symfony\Component\Yaml\Yaml::dump';

				if($this->yamlDecode === null)
					$this->yamlDecode = array('Symfony\Component\Yaml\Yaml', 'parse');
			
				if($this->yamlEncode === null)
					$this->yamlEncode = array('Symfony\Component\Yaml\Yaml', 'dump');
			}
			else
			{
				// Test for Spyc YAML //
				$found_spyc_codex = class_exists('Spyc');
				// echo '<pre>'.__CLASS__.' $found_spyc_codex: '.var_export($found_spyc_codex, true)."</pre>\n";

				if( $found_spyc_codex )
				{
					// use Spyc;
					$this->implimentations->decoders[] = 'Spyc::YAMLLoad';
					$this->implimentations->encoders[] = 'Spyc::YAMLDump';

					if($this->yamlDecode === null)
						$this->yamlDecode = array('Spyc', 'YAMLLoad');
					
					if($this->yamlEncode === null)
						$this->yamlEncode = array('Spyc', 'YAMLDump');
				}
			}
		}
	}// end function __construct()
	
	public function decode($src)
	{
		// echo '<pre>'.__METHOD__.' $src: '.var_export($src, true)."</pre>\n";
		return forward_static_call($this->yamlDecode, $src);
	}
	
	public function encode($data)
	{
		// return $this->yamlEncode($src);
		return forward_static_call($this->yamlEncode, $src);
	}
	
	private function phpYamlDecode($src)
	{
		if(preg_match('/^https?:\/\//', $src))
		{
			if(in_array('yaml_parse_url', $this->implimentations->decoders))
				$yaml = yaml_parse_url($src);// yaml_parse_url($url, $position=0, &$numberOfDocs, callbacks);
			else
			{
				$yaml = file_get_contents($src);
				$yaml = yaml_parse($yaml);
			}
		}
		else if(file_exists($src))
		{
			if(is_readable($src))
			{
				if(in_array('yaml_parse_file', $this->implimentations->decoders))
					$yaml = yaml_parse_file($src);// yaml_parse_file($path, $position=0, &$numberOfDocs, callbacks);
				else
				{
					$yaml = file_get_contents($src);
					$yaml = yaml_parse($yaml);
				}
			}
			else
			{
				// ERROR
			}
		}
		else
		{
			$yaml = yaml_parse($src);
		}
		
		return $yaml;
	}
	
	/**
	 * The Syck extension is YAML 1.0 compliant.
	 *
	 */
	private function syckDecode($src)
	{
		if(preg_match('/^https?:\/\//', $src))
		{
			$yaml = file_get_contents($src);
			$yaml = syck_load($yaml);
		}
		else if(file_exists($src))
		{
			if(is_readable($src))
			{
				$yaml = file_get_contents($src);
				$yaml = syck_load($yaml);
			}
			else
			{
				// ERROR
			}
		}
		else
		{
			$yaml = syck_load($src);
		}
		
		return $yaml;
	}
}
