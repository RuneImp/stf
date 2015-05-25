<?php

class STF_Print
{
	public function r($obj, $depth=0)
	{
		$result = '';
		$indent = str_repeat("        ", $depth);

		switch(gettype($obj))
		{
			case 'boolean':
				$result = $obj ? 'TRUE' : 'FALSE';
				break;
			case 'integer':
			case 'double':
			case 'float':
				$result = $obj;
				break;
			case 'string':
				$result = '"'.$obj.'"';
				break;
			case 'null':
			case 'NULL':
				$result = 'NULL';
				break;
			case 'array':
				$result = "Array\n";
				$result .= $indent."(\n";
				foreach($obj as $k=>$v)
				{
					$result .= $indent.'    [';
					if(gettype($k) == 'string')
						$result .= '"'.$k.'"';
					else
						$result .= $k;
					$result .= '] => '.$this->r($v, $depth+1)."\n";
				}
				$result .= $indent.")\n";
				break;
			case 'object':
				$className = get_class($obj);
				$result = "{$className} Object\n";
				$result .= $indent."(\n";
				foreach($obj as $k=>$v)
					$result .= $indent.'    ['.$k.'] => '.$this->r($v, $depth+1)."\n";
				
				$result .= $indent.")\n";
				break;
			default:
				$result = 'unknown type';
		}
		return $result;
	}
}
?>