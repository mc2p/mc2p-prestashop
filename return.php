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

$mc2p = new Mc2p();

if ((!$cartId = Tools::getValue('cartId'))) {
    Tools::redirect('index.php');
}

$order = new Order(Order::getOrderByCartId($cartId));

if (!$order || !is_object($order) || $order->id === null) {
    Tools::redirect('index.php');
}

Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $order->id_cart . '&id_module=' .
    $mc2p->id . '&id_order=' . $order->id . '&key=' . $order->secure_key);
