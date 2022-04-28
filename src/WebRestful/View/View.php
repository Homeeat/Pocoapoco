<?php


namespace Ntch\Pocoapoco\WebRestful\View;

use Ntch\Pocoapoco\WebRestful\View\Base as ViewBase;

class View
{

    /**
     * @var object
     */
    public object $request;

    /**
     * Construct
     *
     * @param string $absoluteFile
     * @param array $data
     */
    public function __construct(string $absoluteFile, array $data)
    {
        $viewBase = new ViewBase();
        $request = new \stdClass();

        $request->uri = $viewBase->getUri();
        $request->query = $viewBase->getQuery();
        $request->cookies = $viewBase->getCookie();

        $this->request = $request;
        $this->data = $data;

        include $absoluteFile;
    }

}