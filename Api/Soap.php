<?php

namespace JakubZapletal\Component\Gopay\Api;

use SoapClient;
use SoapFault;
use Exception;

class Soap
{
    const PAYMENT_CREATED = 'CREATED';
    const PAYMENT_METHOD_CHOSEN = 'PAYMENT_METHOD_CHOSEN';
    const PAYMENT_PAID = 'PAID';
    const PAYMENT_AUTHORIZED = 'AUTHORIZED';
    const PAYMENT_CANCELED = 'CANCELED';
    const PAYMENT_TIMEOUTED = 'TIMEOUTED';
    const PAYMENT_REFUNDED = 'REFUNDED';
    const PAYMENT_PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
    const PAYMENT_FAILED = 'FAILED';

    const CALL_COMPLETED = 'CALL_COMPLETED';
    const CALL_FAILED = 'CALL_FAILED';

    const RECURRENCE_CYCLE_MONTH = 'MONTH';
    const RECURRENCE_CYCLE_WEEK = 'WEEK';
    const RECURRENCE_CYCLE_DAY = 'DAY';
    const RECURRENCE_CYCLE_ON_DEMAND = 'ON_DEMAND';

    const CALL_RECURRENCE_CANCEL_RESULT_ACCEPTED = 'ACCEPTED';
    const CALL_RECURRENCE_CANCEL_RESULT_FINISHED = 'FINISHED';
    const CALL_RECURRENCE_CANCEL_RESULT_FAILED = 'FAILED';

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Url path to GoPay API
     *
     * @var string
     */
    protected $apiUrl = 'https://gate.gopay.cz/axis/EPaymentServiceV2?wsdl';

    /**
     * Url path to test GoPay API
     *
     * @var string
     */
    protected $apiUrlTest = 'https://testgw.gopay.cz/axis/EPaymentServiceV2?wsdl';

    /**
     * @var SoapClient
     */
    protected $client;





    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }


    protected function createPayment(array $paymentCommand)
    {
        try {
            $paymentStatus = $this->client->__call('createPayment', ['paymentCommand' => $paymentCommand]);

            if ($paymentStatus->result == self::CALL_COMPLETED
                && $paymentStatus->sessionState == self::PAYMENT_CREATED
                && $paymentStatus->paymentSessionId > 0
            ) {

                return $paymentStatus->paymentSessionId;

            } else {
                throw new Exception("Create payment failed: " . $paymentStatus->resultDescription);
            }
        } catch (SoapFault $e) {
            throw new Exception("Communication with WS failed");
        }
    }

    /**
     * @return SoapClient
     */
    protected function getClient()
    {
        if ($this->client === null) {
            ini_set('soap.wsdl_cache_enabled','0');

            if ($this->debug === true) {
                $api = $this->apiUrlTest;
            } else {
                $api = $this->apiUrl;
            }

            $this->client = new SoapClient($api);
        }

        return $this->client;
    }
} 