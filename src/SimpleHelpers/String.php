<?php

namespace SimpleHelpers;

class String
{
    /**
     * @param integer $quantity
     *
     * @return string
     */
    static public function newLine($quantity = 1)
    {
        return str_repeat(PHP_EOL, $quantity);
    }

    /**
     * @see http://stackoverflow.com/questions/25193429/cant-open-downloaded-attachments-from-gmail-api
     *
     * @param string $content
     *
     * @return string
     */
    static public function specialGmailMessageAttachmentDecode($content)
    {
        return base64_decode(
            str_replace(
                ['-', '_'],
                ['+', '/'],
                $content
            )
        );
    }

    /**
     * @param string|integer $key
     * @param integer $length
     *
     * @return boolean
     */
    static public function validateNumber($key, $length = 4)
    {
        if (!is_numeric($key)) {
            return false;
        }

        if (strlen($key) < $length) {
            return false;
        }

        return true;
    }
}