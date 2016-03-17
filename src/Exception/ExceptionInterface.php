<?php

namespace SimpleHelpers\Exception;

interface ExceptionInterface
{
    /**
     * @return array
     */
    public function toLog();
}
