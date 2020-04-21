<?php

namespace Getloy\GetloyMagentoGateway\Api\Data;

interface CallbackResponseInterface
{
    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getMessage();
}
