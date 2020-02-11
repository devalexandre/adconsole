/**
* Method set_$name
* Sample of usage: $pai->$name = $object;
* @param $object Instance of $nameclass
*/
public function set_$name($class $object)
{
    $this->$name = $object;
    $this->$name_id = $object->id;
}

/**
 * Method get_$name
 * Sample of usage: $pai->$name->attribute;
 * @returns $class instance
 */
public function get_$name()
{
    // loads the associated object
    if (empty($this->$name))
        $this->$name = new $class($this->$name_id);

    // returns the associated object
    return $this->$name;
}