/**
* Method addContact
* Add a $class to the $pai
* @param $object Instance of $class
*/
public function add$class($class $object)
{
$this->$name[] = $object;
}

/**
* Method get$class
* Return the $pai' $class's
* @return Collection of $class
*/
public function get$class()
{
return $this->$name;
}

/**
* Reset aggregates
*/
public function clearParts()
{
$this->$name = array();
}