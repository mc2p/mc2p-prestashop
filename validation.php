<?php
/**
 * mychoice2pay Module
 *
 * Copyright (c) 2017 MyChoice2Pay
 *
 * @author    MyChoice2Pay, <www.mychoice2pay.com>
 * @copyright 2017, MyChoice2Pay
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @category  Payment
 * @link      https://www.mychoice2pay.com/
 *
 * Description:
 *
 * Payment module mychoice2pay
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
require_once dirname(__FILE__) . '/mychoice2pay.php';
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

try {
    if ((!$cart = new Cart((int) $transaction['cart_id'])) || !is_object($cart) || $cart->id === null) {
        throw new \Exception(sprintf('Unable to load cart by card id "%d".', $transaction['cart_id']));
    }
    if ((!$customer = new Customer($cart->id_customer))) {
        throw new \Exception('Invalid or missing customer secure key for this transaction.');
    }
} catch (\Exception $e) {
    throw $e;
}

$mychoice2pay = new Mychoice2pay();
$order = new Order(Order::getOrderByCartId($cart->id));

if ($transaction['status'] === 'D') {
    if ($notificationData->getSaleAction() == 'G') {
        $mychoice2pay->validateOrder(
            $cart->id,
            _PS_OS_PAYMENT_,
            $transaction['amount'],
            $mychoice2pay->displayName,
            $mychoice2pay->l(sprintf('MC2P transaction ID: %s.', $transaction['id'])),
            array('transaction_id' => $transaction['id']),
            null,
            false,
            $customer->secure_key,
            null
        );
    }
} else {
    $mychoice2pay->validateOrder($transaction['id'], _PS_OS_ERROR_, 0, $mychoice2pay->displayName, 'Error');
}
