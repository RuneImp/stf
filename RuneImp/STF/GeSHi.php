<?php
/**
 * STF GeSHi Syntax Highlighter
 *
 * @author RuneImp <runeimp@eye.fi>
 * @version 1.0.0
 */
/*
 Change Log:
 -----------
2012-09-06	v1.0.0	Class creation.

 ToDo:
 -----
[ ] Something amazing!
*/
class STF_GeSHi
{
	protected $geshi;
	protected $geshi_lookup;

	public function __construct()
	{
		$this->init();
	}

	protected function init()
	{
		$this->geshi = new GeSHi;
		$this->geshi->set_encoding('UTF-8');

		$this->geshi_lookup = array();
		$this->geshi_lookup['javascript'] = array('js', 'json');

		// $mode = GESHI_ALWAYS;
		// $mode = GESHI_NEVER;
		$mode = GESHI_NEVER;
		$this->geshi->enable_strict_mode($mode);

		$this->geshi->set_overall_class('code');
		// $this->geshi->set_overall_id('dk48ck');
	}

	protected function geshi($src, $lang)
	{
		// GeSHi CSS Styling //
		$preserve_defaults = true;
		$this->geshi->enable_classes();
		// $this->geshi->enable_classes(false);
		// $this->geshi->set_overall_style();
		// $this->geshi->set_line_style('background: #FCFCFC;', 'background: #F0F0F0;', $preserve_defaults);
		// $this->geshi->set_code_style();

		$group = 1;// Logic Statements
		// $this->geshi->set_keyword_group_style($group, $styles, $preserve_defaults);

		$group = 2;// Scalar Types
		// $this->geshi->set_keyword_group_style($group, $styles, $preserve_defaults);

		$group = 3;// Language Functions
		// $this->geshi->set_keyword_group_style($group, $styles, $preserve_defaults);

		$group = 4;// Data Types and Variable Modifiers
		// $this->geshi->set_keyword_group_style($group, $styles, $preserve_defaults);

		$group = 1;// Single-Line Comments
		// $this->geshi->set_comments_style($group, $styles, $preserve_defaults);

		$group = 'MULTI';// Single-Line Comments
		// $this->geshi->set_comments_style($group, $styles, $preserve_defaults);

		// $this->geshi->set_escape_characters_style($styles, $preserve_defaults);
		// $group = 0;
		// $this->geshi->set_symbols_style($styles, $preserve_defaults, $group);
		// $this->geshi->set_strings_style($styles, $preserve_defaults);
		// $this->geshi->set_numbers_style($styles, $preserve_defaults);
		// $this->geshi->set_methods_style($key, $styles, $preserve_defaults);
		// $this->geshi->set_regexps_style($key, $styles, $preserve_defaults);


		// GeSHi Caps Parsing //
		$caps_modifier = GESHI_CAPS_NO_CHANGE;
		// $caps_modifier = GESHI_CAPS_UPPER;
		// $caps_modifier = GESHI_CAPS_LOWER;
		$this->geshi->set_case_keywords($caps_modifier);

		// $key = 1?;
		// $sensitivity = false;// true = Case Sensistive; false = Case Insensitive
		// $this->geshi->set_case_sensitivity($key, $sensitivity);


		// GeSHi Header/Container Type //
		// $this->geshi->set_header_type(GESHI_HEADER_DIV);
		// $this->geshi->set_header_type(GESHI_HEADER_PRE);
		// $this->geshi->set_header_type(GESHI_HEADER_PRE_VALID);
		// $this->geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
		$this->geshi->set_header_type(GESHI_HEADER_PRE);

		// $flag = GESHI_NORMAL_LINE_NUMBERS;
		// $flag = GESHI_FANCY_LINE_NUMBERS;
		$flag = GESHI_NO_LINE_NUMBERS;
		$this->geshi->enable_line_numbers($flag);

		$number = 1;// 1 is the default
		$this->geshi->start_line_numbers_at($number);

		$this->geshi->set_source($src);
		if(!empty($lang))
		{
			$force_reset = true;
			$this->geshi->set_language($lang, $force_reset);
		}

		$parsed_code = $this->geshi->parse_code();

		return $parsed_code;
	}

	public function parse($source, $pattern=null, $language=null)
	{
		if($pattern === null)
		{
			// In Textile if you set two classes they will be converted into
			// lang = The language to parse as
			// type = The HTML wrapper type code, kbd, output, pre or samp
			$pattern = '/<pre class="(?<lang>[^ ]+) *(?<type>[^ "]*)"[^>]*><code>(?<code>[^<]*)<\/code><\/pre>/';
		}

		preg_match_all($pattern, $source, $matches);
		$result = $source;

		foreach($matches[0] as $k=>$v)
		{
			$lang = !empty($matches['lang'][$k]) ? $matches['lang'][$k] : 'code';
			$code = !empty($matches['code'][$k]) ? rtrim($matches['code'][$k]) : '';
			if(!empty($code))
			{
				$code = str_replace("\t", '    ', $code);
				$code = html_entity_decode($code);
				if(!empty($matches['type'][$k]))
				{
					// If type is defined wrap the code in the appropriate HTML container //
					// $code = '<'.$matches['type'][$k].'>'.$code.'</'.$matches['type'][$k].'>';
				}
			}
			$replace = '';

			$code = $this->geshi($code, $lang);
			$replace .= $code;

			$replace .= "\n";

			$result = str_replace($v, $replace, $result);
		}

		return $result;
	}
}
?>