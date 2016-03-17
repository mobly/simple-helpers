<?php

namespace SimpleHelpers\Exception;

trait ExceptionTrait
{
    /**
     * @var array
     */
    protected $context = [];
    
    abstract public function getCode();
    
    abstract public function getMessage();

    abstract public function getFile();

    abstract public function getLine();
    
    abstract public function getTraceAsString();
    
    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function toLog()
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
            'context' => $this->getContext(),
        ];
    }
}
