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

require_once dirname(__FILE__) . '/../../lib/MC2P/MC2PClient.php';

class Mychoice2payPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->page_name = 'checkout';

        parent::initContent();

        if (!$this->isTokenValid()) {
            throw new \Exception(sprintf('%s Error: (Invalid token)', $this->module->displayName));
        }

        if (!$this->module->isPayment()) {
            throw new \Exception(sprintf(
                '%s Error: (Inactive or incomplete module configuration)',
                $this->module->displayName
            ));
        }

        $cart = $this->context->cart;
        $customer = new Customer((int) $cart->id_customer);
        $currency = $this->context->currency;
        $language = $this->context->language;

        if (!Validate::isLoadedObject($customer)
            || !Validate::isLoadedObject($currency)
            || !Validate::isLoadedObject($language)) {
            throw new \Exception(sprintf(
                '%s Error: (Invalid customer, language or currency object)',
                $this->module->displayName
            ));
        }

        $baseUrl = $this->context->shop->getBaseURL(true) . 'modules/' . $this->module->name;
        $url = array(
            'notify' => $baseUrl . '/validation.php',
            'cancel' => $this->context->shop->getBaseURL(true) . 'index.php?controller=order&step=3'
        );

        if (_PS_VERSION_ <= '1.5') {
            $url['return'] = $this->context->shop->getBaseURL(true) . 'index.php?controller=order-confirmation&id_cart='.
                $cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.
                $customer->secure_key;
        } else {
            $url['return'] = $this->context->link->getPageLink('order-confirmation', null, null, array(
                'id_cart' => $cart->id,
                'id_module' => $this->module->id,
                'key' => $customer->secure_key,
                'id_order' => $this->module->currentOrder
            ));
        }

        $mc2p = new MC2P\MC2PClient(Configuration::get('MC2P_KEY'), Configuration::get('MC2P_SECRET_KEY'));

        $transaction = $mc2p->Transaction(
            array(
                "order_id" => $cart->id,
                "currency" => $currency->iso_code,
                "return_url"  => $url['return'],
                "cancel_url" => $url['cancel'],
                "notify_url" => $url['notify'],
                "language" => $language->iso_code,
                "products" => array(
                    array(
                        "amount" => 1,
                        "product" => array(
                            "name" => sprintf(
                                '%09d - %s %s',
                                $cart->id,
                                $customer->firstname,
                                Tools::ucfirst(Tools::strtolower($customer->lastname))
                            ),
                            "price" => $cart->getOrderTotal()
                        )
                    )
                ),
                "extra" => array(
                    "email" => $customer->email,
                    "full_name" => $customer->firstname . ' ' . $customer->lastname
                )
            )
        );

        try {
            $transaction->save();
        } catch (MC2P\InvalidRequestMC2PError $e) {
            throw new \Exception(sprintf('MC2P module configuration error: %s', $e->getMessage()));
        }

        if (Configuration::get('MC2P_IFRAME') == 'active') {
            $this->context->smarty->assign('url', $transaction->getIframeUrl());
            $this->setTemplate('module:mychoice2pay/views/templates/front/iframe.tpl');
        } else {
            Tools::redirect($transaction->getPayUrl());
        }        
    }
}
