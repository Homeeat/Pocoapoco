<?php

namespace Ntch\Pocoapoco\Tools;

trait Uuid
{

    /**
     * Another (ugly) "random or pseudo-random" version of RFC 4122
     *
     * This version calls mt_rand() the fewest possible times.
     * if mt_getrandmax() == 2^31 then this will call mt_rand() 5 times YMMV
     *
     * Personally, I would use mimec's version
     * To handle the large values, we'll keep everything as strings.
     *
     * @see https://www.php.net/manual/en/function.uniqid.php#94959
     *
     * @return string
     */
    public static function uuid(): string
    {

        // Generate 128 bit random sequence
        $randmax_bits = strlen(base_convert(mt_getrandmax(), 10, 2));  // how many bits is mt_getrandmax()
        $x = '';
        while (strlen($x) < 128) {
            $maxbits = (128 - strlen($x) < $randmax_bits) ? 128 - strlen($x) :  $randmax_bits;
            $x .= str_pad(base_convert(mt_rand(0, pow(2,$maxbits)), 10, 2), $maxbits, "0", STR_PAD_LEFT);
        }

        // break into fields
        $a = array();
        $a['time_low_part'] = substr($x, 0, 32);
        $a['time_mid'] = substr($x, 32, 16);
        $a['time_hi_and_version'] = substr($x, 48, 16);
        $a['clock_seq'] = substr($x, 64, 16);
        $a['node_part'] =  substr($x, 80, 48);

        // Apply bit masks for "random or pseudo-random" version per RFC
        $a['time_hi_and_version'] = substr_replace($a['time_hi_and_version'], '0100', 0, 4);
        $a['clock_seq'] = substr_replace($a['clock_seq'], '10', 0, 2);

        // Format output
        return sprintf('%s-%s-%s-%s-%s',
            str_pad(base_convert($a['time_low_part'], 2, 16), 8, "0", STR_PAD_LEFT),
            str_pad(base_convert($a['time_mid'], 2, 16), 4, "0", STR_PAD_LEFT),
            str_pad(base_convert($a['time_hi_and_version'], 2, 16), 4, "0", STR_PAD_LEFT),
            str_pad(base_convert($a['clock_seq'], 2, 16), 4, "0", STR_PAD_LEFT),
            str_pad(base_convert($a['node_part'], 2, 16), 12, "0", STR_PAD_LEFT));
    }


    /**
     * Sql id for PK
     *
     * @return string
     */
    public static function sqlId(): string
    {
        return date('U') . substr(microtime(FALSE), 2, 6);
    }

}