<?php namespace Codecycler\MollieShopaholic\Classes\Event;

use DB;
use Lang;
use Cache;
use Omnipay\Omnipay;
use Lovata\Shopaholic\Models\Settings;
use Lovata\OrdersShopaholic\Classes\Processor\CartProcessor;

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
        // Order status id
        $iResponseStatus = $this->obPaymentMethod
            ->gateway_property[$this->arPayment['status'] . 'Status'];

        // Check if order status is different from the known status
        $bOrderStatusChanged = ($this->obOrder->status_id != $iResponseStatus);

        // Clear cart if order is paid
        if ($this->arPayment['status'] == 'paid') {
            CartProcessor::instance()->clear();
        }

        //
        if ($this->arPayment['status'] == 'paid' && !(bool) Settings::getValue('decrement_offer_quantity') && $bOrderStatusChanged) {
            if (Cache::has('payment_' . $this->arPayment['id'])) {
                return;
            }

            foreach ($this->obOrder->order_position as $obPosition) {
                $obOffer = $obPosition->offer;

                try {
                    $obOffer->quantity = $obOffer->quantity -= $obPosition->quantity;
                    $obOffer->save();
                } catch (\Exception $obException) {
                    \Log::debug($obException);
                }
            }

            Cache::forever('payment_' . $this->arPayment['id'], true);
        }

        // Order status does not exist in the config of the gateway
        if (!isset($this->obPaymentMethod->gateway_property[$this->arPayment['status'] . 'Status'])) {
            $this->obOrder->status_id = $this->obPaymentMethod->gateway_property['openStatus'];
            $this->obOrder->payment_response = $this->arPayment;
            $this->obOrder->save();

            return;
        }

        $this->obOrder->status_id = $this->obPaymentMethod->gateway_property[$this->arPayment['status'] . 'Status'];
        $this->obOrder->payment_response = $this->arPayment;

        $this->obOrder->save();
    }
}