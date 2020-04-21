<?php

namespace Getloy\GetloyMagentoGateway\Model;

use Getloy\GetloyMagentoGateway\Api\Data\CallbackResponseInterface;

class CallbackResponse implements CallbackResponseInterface
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $status
     * @param string $message
     */
    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->message;
    }
}
