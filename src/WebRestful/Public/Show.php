<?php


namespace Ntch\Pocoapoco\WebRestful\Public;

class Show
{

    public function __construct($absoluteFile)
    {
        include $absoluteFile;
    }

}