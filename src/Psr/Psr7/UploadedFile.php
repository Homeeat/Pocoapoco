<?php


namespace Ntch\Pocoapoco\Psr\Psr7;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{

    /**
     * @inheritDoc
     */
    public function getStream()
    {
        // TODO: Implement getStream() method.
    }

    /**
     * @inheritDoc
     */
    public function moveTo($targetPath)
    {
        // TODO: Implement moveTo() method.
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        // TODO: Implement getError() method.
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename()
    {
        // TODO: Implement getClientFilename() method.
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType()
    {
        // TODO: Implement getClientMediaType() method.
    }
}