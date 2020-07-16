<?php namespace Codecycler\MollieShopaholic\Classes\Event;

use Omnipay\Omnipay;

class ProcessPayment
{
    protected $obOrder;

    protected $arPayment;

    protected $obGateway;

    protected $obPaymentMethod;

    public function handle($obOrder, $obPaymentMethod)
    {
        $this->obOrder = $obOrder;
        $this->obPaymentMethod = $obPaymentMethod;

        //
        $this->obGateway = Omnipay::create($this->obPaymentMethod->gateway_id);

        //
        $this->fetchTransaction();

        //
        $this->updateOrderStatus();
    }

    protected function fetchTransaction()
    {
        $arFetchTransactionRequestData = [
            'apiKey' => $this->obPaymentMethod->gateway_property['apiKey'],
        ];

        $obRequest = $this->obGateway->fetchTransaction($arFetchTransactionRequestData);
        $obResponse = $obRequest->sendData(['id' => $this->obOrder->payment_token]);

        $this->arPayment = $obResponse->getData();
    }

    protected function updateOrderStatus()
    {
        if (!isset($this->obPaymentMethod->gateway_property[$this->arPayment['status'] . 'Status'])) {
            $this->obOrder->status_id = $this->obPaymentMethod->gateway_property['openStatus'];
            $this->obOrder->save();

            return;
        }

        $this->obOrder->status_id = $this->obPaymentMethod->gateway_property[$this->arPayment['status'] . 'Status'];

        $this->obOrder->save();
    }
}