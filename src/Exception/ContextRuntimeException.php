<?php

namespace SimpleHelpers\Exception;

use Exception;
use RuntimeException;
use SimpleHelpers\Http\StatusCode;

class ContextRuntimeException extends RuntimeException implements ExceptionInterface
{
    use ExceptionTrait;

    /**
     * @param string $message
     * @param integer $code
     * @param array $context
     * @param Exception|null $previous
     */
    public function __construct(
        $message = '', 
        $code = StatusCode::INTERNAL_SERVER_ERROR, 
        array $context = [], 
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->setContext($context);
    }
}
