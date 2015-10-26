<?php

namespace SimpleHelpers;

class Cli
{
    const COLOR_RED = 'Red';
    const COLOR_RED_BOLD = 'Red';
    const COLOR_RED_DIM = 'Red';
    const COLOR_GREEN = 'Red';
    const COLOR_GREEN_BOLD = 'Red';
    const COLOR_GREEN_DIM = 'Red';
    const COLOR_YELLOW = 'Red';
    const COLOR_YELLOW_BOLD = 'Red';
    const COLOR_YELLOW_DIM = 'Red';

    /**
     * @param string $text
     * @param string $color
     *
     * @return boolean
     */
    static public function writeOutput($text, $color = '')
    {
        switch ($color) {
            case self::COLOR_RED:
                $out = "[0;31m";
                break;
            case self::COLOR_RED_BOLD:
                $out = "[1;31m";
                break;
            case self::COLOR_RED_DIM:
                $out = "[2;31m";
                break;
            case self::COLOR_GREEN:
                $out = "[0;32m";
                break;
            case self::COLOR_GREEN_BOLD:
                $out = "[1;32m";
                break;
            case self::COLOR_GREEN_DIM:
                $out = "[2;32m";
                break;
            case self::COLOR_YELLOW:
                $out = "[0;33m";
                break;
            case self::COLOR_YELLOW_BOLD:
                $out = "[1;33m";
                break;
            case self::COLOR_YELLOW_DIM:
                $out = "[2;33m";
                break;
            default:
                echo String::newLine() . $text;

                return true;
        }

        echo String::newLine() . chr(27) . $out . $text . chr(27) . "[0m";

        return true;
    }

    /**
     * @param string $prompt
     * @param array $validInputs
     * @param string $default
     *
     * @return string
     */
    static public function readInput($prompt, array $validInputs, $default = '')
    {
        while (!isset($input) || !in_array($input, $validInputs)) {
            echo $prompt;

            $input = strtolower(trim(fgets(STDIN)));

            if (empty($input) && !empty($default)) {
                $input = $default;
            }
        }

        return $input;
    }

    /**
     * @param string $command
     *
     * @return array
     */
    static public function execute($command)
    {
        $output = array();
        $return = 0;

        ob_start();
        exec($command, $output, $return);
        $std = ob_get_contents();
        ob_end_clean();

        return array(
            'return' => $return,
            'output' => $output,
            'std' => $std,
        );
    }
}