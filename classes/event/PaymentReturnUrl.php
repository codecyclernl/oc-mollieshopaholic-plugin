<?php namespace Codecycler\MollieShopaholic\Classes\Event;

use Cms\Classes\Page;
use October\Rain\Router\Router;

class PaymentReturnUrl
{
    public function handle($obOrder, $obPaymentMethod)
    {
        if ($obPaymentMethod->gateway_id != 'Mollie') {
            return;
        }

        $obRouter = new Router;
        $obPage = Page::find($obPaymentMethod->gateway_property['redirectUrl']);

        return  $obRouter->urlFromPattern($obPage->url, $obOrder->toArray());
    }
}