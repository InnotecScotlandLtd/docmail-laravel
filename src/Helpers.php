<?php

namespace Softlabs\Docmail;

trait Helpers {
    /**
     * UC First for Multi-Byte String
     * @param  string $string
     * @return string
     */
    private function mbUcfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * Converts a value to a presentable GBP format.
     *
     * @param  integer $data The data to be converted to GBP format.
     * @param  string $returnVal A value to return if the conversion is impossible.
     * @return string The formatted GBP string.
     */
    private function gbp($data, $returnVal='-')
    {
        if (is_null($data)) {
            return $returnVal;
        }

        if ( ! is_float($data)) {
            $data = floatval($data);
        }

        return (is_numeric($data) ? '£' . number_format($data, 2) : $data);
    }

    /**
     * Get information of a file
     * @param  string $filePath
     * @return array
     */
    private function fileInfo($filePath)
    {
        $pathInfo = pathinfo($filePath);

        $file = [];

        $file['name']      = $pathInfo['filename'];
        $file['extension'] = $pathInfo['extension'];
        $file['full_name'] = $pathInfo['filename'] . "." . $file['extension'];

        return $file;
    }
}