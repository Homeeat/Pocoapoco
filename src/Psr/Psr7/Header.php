<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Psr\Psr7;

trait Header
{

    /**
     * Make sure the header complies with RFC 7230.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.6
     *
     * @param string $name
     *
     * @return boolean
     */
    public function normalizationHeaderName(string $name): bool
    {
        if (!is_string($name) || !preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $name)) {
            return false;
        }
        return true;
    }

    /**
     * Make sure the header complies with RFC 7230.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.6
     *
     * @param string $value
     *
     * @return boolean
     */
    public function normalizationHeaderValue(string $value): bool
    {
        if ((!is_string($value) && !is_numeric($value)) || !preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", $value)) {
            return false;
        }
        return true;
    }

}