<?php
/**
 *
 *
 */
namespace RuneImp\STF\Net\URI;

interface iURIParser
{
	public function __construct($url=false);
	public function getAuthority();
	public function getData($key=null);
	public function getHost();
	public function getMethod();
	public function getPath();
	public function getProtocol();
	public function getScheme();
	public function parseURI($uri);
}