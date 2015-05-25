<?php
/**
 * STF Abstract View
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-23	v0.1.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] _____
 * [ ] _____
 * [ ] _____
 * [ ] _____
 * [ ] _____
 */
namespace RuneImp\STF\MVC;

class AbstractModel implements iModel
{
	//  CLASS CONSTANTS //
	const NODATA = 'RuneImp.STF.MVC.Model.NoData';

	// CLASS PROPERTIES //
	protected $config;
	protected $data;
	protected $view;

	// CLASS CONSTRUCTOR //
	public function __construct(&$config)
	{
		$this->config = $config;
	}

	/**
	 * Initialize this class with the provided view to update
	 *
	 * @param	&$view	The view to send updates to.
	 * @return	void
	 */
	public function init(&$view)
	{
		$this->view = &$view;
	}

	/**
	 * Method to retrieve data from this model.
	 *
	 * @param	$key	The data key to access the data values of.
	 * @return	A value of any type possible.
	 */
	public function getData($key)
	{
		return ( isset($data[$key]) ) ? $data[$key] : null;
	}

	/**
	 * Method to accept updates from the controller
	 *
	 * @param	$data	The data package to update the model with.
	 * @return	Boolean true on successful update
	 */
	public function update($data)
	{
		//
	}
}