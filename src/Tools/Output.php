<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Tools;

use DOMDocument;
use Exception;
use LaLit\XML2Array;
use LaLit\Array2XML;

trait Output
{

    /**
     * Curl.
     *
     * @param string $method
     * @param string $url
     * @param string|null $data
     * @param array $header
     * @param int $timeout
     * @param bool $showHeader
     *
     * @return array
     */
    public function curl(string $method, string $url, ?string $data = null, array $headers = [], int $timeout = 10, bool $showHeader = false): array
    {
        $method = strtoupper($method);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HEADER, $showHeader);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 0);

        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            $res['status'] = 'ERROR';
            $res['result'] = curl_error($curl);
        } else {
            $res['status'] = 'SUCCESS';
            $res['header'] = curl_getinfo($curl);
            $res['result'] = $result;
        }
        curl_close($curl);

        return $res;
    }

    /**
     * Array to json.
     * 【Illustrate】
     *  array：input data.
     *  object：convert to object.
     *  convert：string to number.
     *  escape：Add escape symbols.
     *
     * @param array $array
     * @param int $object
     * @param int $convert
     * @param int $escape
     *
     * @return string
     */
    public function arrayToJson(array $array, int $object = 0, int $convert = 1, int $escape = 1): string
    {
        // 256 JSON_UNESCAPED_UNICODE - 中文編碼
        //  16 JSON_FORCE_OBJECT - 轉成索引
        //  32 JSON_NUMERIC_CHECK - 依據值的型態進行轉換 string -> int
        //  64 JSON_UNESCAPED_SLASHES - 不加跳脫符號

        $flags = 256;
        $flags += $object ? 16 : null;
        $flags += $convert ? 32 : null;
        $flags += $escape ? 64 : null;

        return json_encode($array, $flags);
    }

    /**
     * Json to array.
     *
     * @param string $json
     *
     * @return array
     */
    public function jsonToArray(string $json): array
    {
        return json_decode(trim($json), true);
    }

    /**
     * Array to xml.
     *
     * @param string $root
     * @param array $array
     *
     * @return false|string
     * @throws Exception
     */
    public function arrayToXml(string $root, array $array): bool|string
    {
        $xml = Array2XML::createXML($root, $array);
        return $xml->saveHTML();
    }


    /**
     * Xml to array.
     *
     * @param string $xml
     *
     * @return array
     * @throws Exception
     */
    public function xmlToArray(string $xml): array
    {
        return XML2Array::createArray($xml);
    }


}