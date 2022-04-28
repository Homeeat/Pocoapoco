<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

use PHPunit\Framework\TestCase;
use Ntch\Pocoapoco\Psr\Psr7\Message;

class MessageTest extends TestCase
{

//    /**
//     * @dataProvider protocolVersionProvider
//     * @param $version
//     */
//    public function testWithProtocolVersion($version){
//        $this->assertEquals(['1.0', '1.1', '2.0', '3.0'], $version);
//    }
//
//    public function protocolVersionProvider(): string
//    {
//        return '1.2';
//    }

//    /**
//     * @depends testWithProtocolVersion
//     */
//    public function testGetProtocolVersion()
//    {
//        $message = new Message();
//        $version = '1.1';
//        $message->withProtocolVersion($version);
//        $result = $message->getProtocolVersion();
//        $this->assertEquals('1.1', $result);
//    }

    public function testWithProtocolVersion()
    {
        $message = new Message();
        $version = '1.1';
        $message->withProtocolVersion($version);
        $result = $message->getProtocolVersion();
        $this->assertEquals('1.1', $result);
    }

}