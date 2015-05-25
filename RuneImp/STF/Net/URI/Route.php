<?php
/**
 * SimpleThingFramework URI Route class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-06	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
class STF_Net_URI_Route extends STF_Base implements IteratorAggregate
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';

	const ROUTE_TYPE_DYNAMIC_RUN = 'stf.net.uri.route.type.Dynamic.Run';
	const ROUTE_TYPE_DYNAMIC_EXIT = 'stf.net.uri.route.type.Dynamic.Exit';
	const ROUTE_TYPE_NORMAL = 'stf.net.uri.route.type.Normal';
	const ROUTE_TYPE_REDIRECT = 'stf.net.uri.route.type.Redirect';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $path;
	protected $content;
	protected $type;
	protected $is_dir;
	protected $meta_data;
	protected $route;

	public function __construct($route, $is_dir=false, $type=null, $path=null, $meta_data=null)
	{
		parent::__construct();

		$this->route = $route;
		$this->is_dir = $is_dir;// if is_dir and path has no content list files within path
		$this->path = $path;
		$this->type = !empty($type) ? $type : self::ROUTE_TYPE_NORMAL;
		if(!is_null($meta_data) && !is_array($meta_data))
			throw new STF_Net_URI_RouteException('The $meta_data argument must be a named array. Argument type '.gettype($meta_data).' not supported.', 1);
		$this->meta_data = !is_null($meta_data) ? $meta_data : array();

		// 1 = readable; 2 = writable; 4 = isset //
		$this->props['content'] = 5;
		$this->props['is_dir'] = 5;
		$this->props['meta_data'] = 5;
		$this->props['path'] = 5;
		$this->props['route'] = 5;
		$this->props['type'] = 5;
	}

	public function addContent($content)
	{
		$this->content = $content;
	}

	public function addMetaData($meta_data)
	{
		$this->meta_data = $meta_data;
	}

	public function getIterator()
	{
		$obj = new stdClass();
		$obj->content = $this->content;
		$obj->type = $this->type;
		$obj->is_dir = $this->is_dir;
		$obj->meta_data = $this->meta_data;
		$obj->path = $this->path;
		$obj->route = $this->route;
		// foreach(new ArrayIterator($this) as $k=>$v)
		// 	$obj->{$k} = $v;

		return $obj;
	}

	/**
	 * Method to retrieve any meta_data stored in this Route.
	 * Will optionally merge with a supplied array or Traversable object.
	 * 
	 * @param  array  $user_data An optional array or instance of a Traversable object to merge with the internal meta_data.
	 * @return array             Named array of all internal and (optionally) external meta_data.
	 */
	public function getMetaData($user_data=array())
	{
		if(!is_array($user_data) && !$user_data instanceof Traversable)
			throw new STF_Net_URI_RouteException('Supplied argument must be an array a Traversable object or not specified. Argument type '.gettype($user_data).' not supported.', 2);

		$result = array();

		foreach($user_data as $k=>$v)
			$result[$k] = $v;

		foreach($this->meta_data as $k=>$v)
			$result[$k] = $v;

		return $result;
	}
}

class STF_Net_URI_RouteException extends Exception{}

?>