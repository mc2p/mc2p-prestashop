<?php
/**
 * mc2p Module
 *
 * Copyright (c) 2017 MyChoice2Pay
 *
 * @category  Payment
 * @author    MyChoice2Pay, <www.mychoice2pay.com>
 * @copyright 2017, MyChoice2Pay
 * @link      https://www.mychoice2pay.com/
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * Description:
 *
 * Payment module mc2p
 *
 * --
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to hola@mychoice2pay.com so we can send you a copy immediately.
 */

require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/mc2p.php';
require_once dirname(__FILE__) . '/lib/MC2P/MC2PClient.php';

$key = Configuration::get('MC2P_KEY');
$secretKey = Configuration::get('MC2P_SECRET_KEY');
$mc2p = new MC2P\MC2PClient($key, $secretKey);

$json = (array)json_decode(Tools::file_get_contents('php://input'));

$notificationData = $mc2p->NotificationData($json, $mc2p);
$mc2pTransaction = $notificationData->getTransaction();

if (!$mc2pTransaction) {
    throw new \Exception('Invalid notification, nothing todo.');
}

/* Join transaction datas */
$transaction = array(
    'id' => $notificationData->getId(),
    'amount' => $mc2pTransaction->total_price,
    'status' => $notificationData->getStatus(),
    'cart_id' => $notificationData->getOrderId()
);

$customer = new Customer((int)$cart->id_customer);

try {
    if ((!$cart = new Cart((int) $transaction['cart_id'])) || !is_object($cart) || $cart->id === null) {
        throw new \Exception(sprintf('Unable to load cart by card id "%d".', $transaction['cart_id']));
    }
} catch (\Exception $e) {
    throw $e;
}

$mc2p = new Mc2p();
$order = new Order(Order::getOrderByCartId($cart->id));

if ($transaction['status'] === 'D') {
    $mc2p->validateOrder($cart->id, _PS_OS_PAYMENT_, $transaction['amount'], $mc2p->displayName,
        $mc2p->l(sprintf('MC2P transaction ID: %s.', $transaction['id'])), array(
            'transaction_id' => $transaction['id']
        ), null, false, $customer->secure_key, null);
} else {
    $mc2p->validateOrder($transaction['id'], _PS_OS_ERROR_, 0, $mc2p->displayName, 'Error');
}
