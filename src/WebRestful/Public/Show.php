<?php


namespace Ntch\Pocoapoco\WebRestful\Public;

class Show
{

    public function __construct($absoluteFile, string $uuid)
    {
        $this->data['uuid'] = $uuid;
        include $absoluteFile;
    }

}