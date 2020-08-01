<?php namespace Codecycler\MollieShopaholic;

use Event;
use Omnipay\Omnipay;
use System\Classes\PluginBase;
use Lovata\OmnipayShopaholic\Classes\Helper\PaymentGateway;
use Codecycler\MollieShopaholic\Classes\Event\ProcessPayment;
use Codecycler\MollieShopaholic\Classes\Event\ExtendFieldHandler;
use Codecycler\MollieShopaholic\Classes\Event\PaymentReturnUrl;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function boot()
    {
        $factory = Omnipay::getFactory();
        $factory->register('Mollie');

        Event::subscribe(ExtendFieldHandler::class);
        Event::listen(PaymentGateway::EVENT_PROCESS_RETURN_URL, ProcessPayment::class);
        Event::listen(PaymentGateway::EVENT_GET_PAYMENT_GATEWAY_RETURN_URL, PaymentReturnUrl::class);
    }
}
