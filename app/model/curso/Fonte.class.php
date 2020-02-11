<?php
/**
 * generate by adconsole 0.1
 * @author: Indev Web www.dein.net.br
 * @mail team@indev.net.br
 */

class Fonte extends TRecord
{
    const TABLENAME = 'Fonte';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('nome'); 
	
    }

    

    

     

}