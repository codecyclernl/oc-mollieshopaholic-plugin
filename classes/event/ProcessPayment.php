<?php namespace Codecycler\MollieShopaholic\Classes\Event;

use DB;
use Lang;
use Omnipay\Omnipay;
use Kharanenka\Helper\Result;
use Lovata\Shopaholic\Models\Settings;

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
        if ($this->arPayment['status'] == 'paid' && !(bool) Settings::getValue('decrement_offer_quantity')) {
            foreach ($this->obOrder->order_position as $obPosition) {
                $obOffer = $obPosition->offer;

                try {
                    DB::table('lovata_shopaholic_offers')
                        ->where('id', $obOffer->id)
                        ->update([
                            'quantity' => $obOffer->quantity -= $obPosition->quantity,
                        ]);
                } catch (\Exception $obException) {
                    \Log::debug($obException);
                }
            }
        }

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