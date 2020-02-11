    /**
    * Load the object and its aggregates
    * @param $id object ID
    */
    public function load($id)
    {
    $loadComposite

    $loadAggregate


    // load the object itself
    return parent::load($id);
    }

    /**
    * Store the object and its aggregates
    */
    public function store()
    {
    // store the object itself
    parent::store();

    $saveComposite
    $saveAggregate


    }

    /**
    * Delete the object and its aggregates
    * @param $id object ID
    */
    public function delete($id = NULL)
    {
    $deleteComposite
    $deleteAggregate

    // delete the object itself
    parent::delete($id);
    }