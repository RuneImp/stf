<?php
/**
 * STF Page Domain Controller
 */

interface Page
{
	public function header();
	public function content();
	public function footer();
}