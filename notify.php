<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/mc2p.php');

$autoloader_param = __DIR__ . '/lib/MC2P/MC2PClient.php';
// Load up the MC2P library
try {
    require_once $autoloader_param;
} catch (\Exception $e) {
    throw new \Exception('The MC2P payment plugin was not installed correctly or the files are corrupt. Please reinstall the plugin. If this message persists after a reinstall, contact hola@mychoice2pay.com with this message.');
}

if ( !empty( $_REQUEST ) ) {
    $json = (array)json_decode(file_get_contents('php://input'));

    if ( !empty( $json ) ) {

        $key = Configuration::get('MC2P_KEY');
        $secretKey = Configuration::get('MC2P_SECRET_KEY');
        $mc2p = new MC2P\MC2PClient($key, $secretKey);

        $notificationData = $mc2p->NotificationData($json, $mc2p);
        $transaction = $notificationData->getTransaction();

        if ($notificationData->getStatus() == 'D') {
            $context = Context::getContext();
			$cart = new Cart($notificationData->getOrderId());
			$mc2p = new mc2p();

			if ($cart->id_customer == 0
				|| $cart->id_address_delivery == 0
				|| $cart->id_address_invoice == 0
				|| !$redsys->active) {
				Tools::redirect('index.php?controller=order&step=1');
			}

			$customer = new Customer((int)$cart->id_customer);

			// Done
			Context::getContext()->customer = $customer;
			$address = new Address((int)$cart->id_address_invoice);
			Context::getContext()->country = new Country((int)$address->id_country);
			Context::getContext()->customer = new Customer((int)$cart->id_customer);
			Context::getContext()->language = new Language((int)$cart->id_lang);
			Context::getContext()->currency = new Currency((int)$cart->id_currency);

            if (!Validate::isLoadedObject($customer)) {
				Tools::redirect('index.php?controller=order&step=1');
			}
			// Total
			$totalCart = $cart->getOrderTotal(true, Cart::BOTH);

			// ID Currency
			$currencyOrigin = new Currency($cart->id_currency);

			if ($currencyOrigin == $transaction->currency && $totalCart == $transaction->total_price) {
				$mailvars['transaction_id'] = (int)$transaction->token;
				$mc2p->validateOrder($notificationData->getOrderId(), _PS_OS_PAYMENT_, $totalCart, $mc2p->displayName, null, $mailvars, (int)$cart->id_currency, false, $customer->secure_key);
            } else {
                $mc2p->validateOrder($notificationData->getOrderId(), _PS_OS_ERROR_, 0, $mc2p->displayName, 'Error');
            }
        } else if ( $notification_data->getStatus() == 'C' ) {
            $mc2p->validateOrder($notificationData->getOrderId(), _PS_OS_ERROR_, 0, $mc2p->displayName, 'Error');
        }
    }
}
?>