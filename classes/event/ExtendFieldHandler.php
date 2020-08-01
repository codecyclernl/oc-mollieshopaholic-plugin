<?php namespace Codecycler\MollieShopaholic\Classes\Event;

use Cms\Classes\Page;
use Lang;
use Omnipay\Omnipay;

use Lovata\OmnipayShopaholic\Classes\Helper\PaymentGateway;

use Lovata\OrdersShopaholic\Models\Status;
use Lovata\OrdersShopaholic\Models\OrderProperty;
use Lovata\OrdersShopaholic\Models\PaymentMethod;
use Lovata\OrdersShopaholic\Controllers\PaymentMethods;

class ExtendFieldHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen('backend.form.extendFields', function ($obWidget) {
            $this->extendPaymentMethodFields($obWidget);
        });
    }

    /**
     * Extend settings fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function extendPaymentMethodFields($obWidget)
    {
        // Only for the Settings controller
        if (!$obWidget->getController() instanceof PaymentMethods || $obWidget->isNested) {
            return;
        }

        // Only for the Settings model
        if (!$obWidget->model instanceof PaymentMethod || empty($obWidget->model->gateway_id) || !class_exists(Omnipay::class)) {
            return;
        }

        //Get payment gateway list
        $arGatewayList = PaymentGateway::getOmnipayGatewayList();
        if (empty($arGatewayList) || !in_array($obWidget->model->gateway_id, $arGatewayList)) {
            return;
        }

        $this->addGatewayPropertyFields($obWidget->model, $obWidget);
    }

    /**
     * Add gateway property list
     * @param PaymentMethod         $obPaymentMethod
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function addGatewayPropertyFields($obPaymentMethod, $obWidget)
    {
        if (!$obPaymentMethod->code == 'mollie') {
            return;
        }

        $arStatusOptions = $this->getStatusOptions();
        $arRedirectOptions = $this->getRedirectOptions();

        $obWidget->addTabFields([
            'gateway_property[openStatus]' => [
                'label' => 'Open status',
                'tab'   => 'Mollie',
                'type'  => 'dropdown',
                'span'  => 'left',
                'options' => $arStatusOptions,
            ],

            'gateway_property[paidStatus]' => [
                'label' => 'Paid status',
                'tab'   => 'Mollie',
                'type'  => 'dropdown',
                'span'  => 'right',
                'options' => $arStatusOptions,
            ],

            'gateway_property[failedStatus]' => [
                'label' => 'Failed status',
                'tab'   => 'Mollie',
                'type'  => 'dropdown',
                'span'  => 'left',
                'options' => $arStatusOptions,
            ],

            'gateway_property[cancelledStatus]' => [
                'label' => 'Canceled status',
                'tab'   => 'Mollie',
                'type'  => 'dropdown',
                'span'  => 'right',
                'options' => $arStatusOptions,
            ],

            'gateway_property[expiredStatus]' => [
                'label' => 'Expired status',
                'tab'   => 'Mollie',
                'type'  => 'dropdown',
                'span'  => 'left',
                'options' => $arStatusOptions,
            ],

            'gateway_property[redirectUrl]' => [
                'label' => 'Redirect URL',
                'tab' => 'Mollie',
                'type' => 'dropdown',
                'span' => 'left',
                'placeholder' => 'Emtpy',
                'options' => $arRedirectOptions,
            ],
        ]);
    }

    protected function getStatusOptions()
    {
        return Status::all()
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getRedirectOptions()
    {
        return Page::all()
            ->pluck('url', 'fileName')
            ->toArray();
    }
}
