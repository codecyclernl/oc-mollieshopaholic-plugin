<?php namespace Codecycler\MollieShopaholic;

use Event;
use System\Classes\PluginBase;
use Lovata\OmnipayShopaholic\Classes\Helper\PaymentGateway;
use Codecycler\MollieShopaholic\Classes\Event\ProcessPayment;
use Codecycler\MollieShopaholic\Classes\Event\ExtendFieldHandler;

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
        Event::subscribe(ExtendFieldHandler::class);
        Event::listen(PaymentGateway::EVENT_PROCESS_RETURN_URL, ProcessPayment::class);
    }
}
