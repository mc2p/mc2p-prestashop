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

if (!defined('_PS_VERSION_')) {
    exit();
}

$autoloader_param = dirname(__FILE__) . '/lib/MC2P/MC2PClient.php';
// Load up the MC2P library
try {
    require_once $autoloader_param;
} catch (\Exception $e) {
    throw new \Exception(
        'The MC2P payment plugin was not installed correctly ' .
        'or the files are corrupt. Please reinstall the plugin. ' .
        'If this message persists after a reinstall, contact ' . '
        hola@mychoice2pay.com with this message.'
    );
}

class Mychoice2pay extends PaymentModule
{
    private $html = '';

    /**
     * Build module
     *
     * @see PaymentModule::__construct()
     */
    public function __construct()
    {
        $this->name = 'mychoice2pay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.6';
        $this->author = 'MyChoice2Pay';
        $this->module_key = '26f33953dc3c6c678b10fb0314dc92b2';
        $this->currencies = true;
        $this->currencies_mode = 'radio';
        $this->is_eu_compatible = 1;
        $this->controllers = array(
            'payment'
        );
        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('MyChoice2Pay');
        $this->description = $this->l('Allows to receive payments from several payment gateways');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

        /* Add configuration warnings if needed */
        if (!Configuration::get('MC2P_KEY')
            || !Configuration::get('MC2P_SECRET_KEY')
            || !Configuration::get('MC2P_TITLE')
            || !Configuration::get('MC2P_DESCRIPTION')
            || !Configuration::get('MC2P_IFRAME')
            || !Configuration::get('MC2P_ICON')) {
            $this->warning = $this->l('Module configuration is incomplete.');
        }
    }

    /**
     * Install module
     *
     * @see PaymentModule::install()
     */
    public function install()
    {
        if (!parent::install()
            || !Configuration::updateValue('MC2P_KEY', '')
            || !Configuration::updateValue('MC2P_SECRET_KEY', '')
            || !Configuration::updateValue('MC2P_TITLE', '')
            || !Configuration::updateValue('MC2P_DESCRIPTION', '')
            || !Configuration::updateValue('MC2P_IFRAME', '')
            || !Configuration::updateValue('MC2P_ICON', '')
            || !$this->registerHook('payment')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('paymentOptions')) {
            return false;
        }
        return true;
    }

    /**
     * Uninstall module
     *
     * @see PaymentModule::uninstall()
     */
    public function uninstall()
    {
        if (!Configuration::deleteByName('MC2P_KEY')
            || !Configuration::deleteByName('MC2P_SECRET_KEY')
            || !Configuration::deleteByName('MC2P_TITLE')
            || !Configuration::deleteByName('MC2P_DESCRIPTION')
            || !Configuration::deleteByName('MC2P_IFRAME')
            || !Configuration::deleteByName('MC2P_ICON')
            || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * Validate submited data
     */
    private function postValidation()
    {
        $this->_errors = array();
        if (Tools::getValue('submitUpdate')) {
            if (!Tools::getValue('MC2P_KEY')) {
                $this->_errors[] = $this->l('mychoice2pay "key" is required.');
            }
            if (!Tools::getValue('MC2P_SECRET_KEY')) {
                $this->_errors[] = $this->l('mychoice2pay "secret key" is required.');
            }
            if (!Tools::getValue('MC2P_TITLE')) {
                $this->_errors[] = $this->l('mychoice2pay "title" is required.');
            }
            if (!Tools::getValue('MC2P_DESCRIPTION')) {
                $this->_errors[] = $this->l('mychoice2pay "description" is required.');
            }
        }
    }

    /**
     * Update submited configurations
     */
    public function getContent()
    {
        $this->html = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitUpdate')) {
            Configuration::updateValue('MC2P_KEY', Tools::getValue('MC2P_KEY'));
            Configuration::updateValue('MC2P_SECRET_KEY', Tools::getValue('MC2P_SECRET_KEY'));
            Configuration::updateValue('MC2P_TITLE', Tools::getValue('MC2P_TITLE'));
            Configuration::updateValue('MC2P_DESCRIPTION', Tools::getValue('MC2P_DESCRIPTION'));
            Configuration::updateValue('MC2P_IFRAME', Tools::getValue('MC2P_IFRAME'));
            Configuration::updateValue('MC2P_ICON', Tools::getValue('MC2P_ICON'));
        }

        $this->postValidation();
        if (isset($this->_errors) && count($this->_errors)) {
            foreach ($this->_errors as $err) {
                $this->html .= $this->displayError($err);
            }
        } elseif (Tools::getValue('submitUpdate') && !count($this->_errors)) {
            $this->html .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $this->html . $this->displayForm();
    }

    /**
     * Build and display admin form for configurations
     */
    private function displayForm()
    {
        $dfl = array(
            'action' => $_SERVER['REQUEST_URI'],
            'img_path' => $this->_path . 'views/img/icons/mc2p.png',
            'path' => $this->_path
        );

        $config = Configuration::getMultiple(array(
            'MC2P_KEY',
            'MC2P_SECRET_KEY',
            'MC2P_TITLE',
            'MC2P_DESCRIPTION',
            'MC2P_IFRAME',
            'MC2P_ICON'
        ));

        $this->context->smarty->assign(array(
            'mychoice2pay' => array(
                'dfl' => $dfl,
                'config' => $config
            )
        ));

        return $this->display(__FILE__, 'views/templates/admin/display_form.tpl');
    }

    /**
     * Build and display payment button
     *
     * @param unknown $params
     * @return boolean|\PrestaShop\PrestaShop\Core\Payment\PaymentOption[]
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->isPayment()) {
            return false;
        }

        $this->context->smarty->assign('path', $this->_path);
        $this->context->smarty->assign('title', Configuration::get('MC2P_TITLE'));
        $this->context->smarty->assign('description', Configuration::get('MC2P_DESCRIPTION'));
        $this->context->smarty->assign('icon', Configuration::get('MC2P_ICON'));

        $paymentOption = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption->setCallToActionText(Configuration::get('MC2P_TITLE'))
            ->setAction($this->context->link->getModuleLink($this->name, 'payment', array(
                'token' => Tools::getToken(false)
            ), true))
            ->setAdditionalInformation($this->context->smarty->fetch(
                'module:mychoice2pay/views/templates/hook/payment_options.tpl'
            ));

        return array($paymentOption);
    }

    /**
     * Build and display payment button
     *
     * @param array $params
     * @return string Templatepart
     */
    public function hookPayment($params)
    {
        if (!$this->isPayment()) {
            return false;
        }

        $this->context->smarty->assign('path', $this->_path);
        $this->context->smarty->assign('static_token', Tools::getToken(false));
        $this->context->smarty->assign('title', Configuration::get('MC2P_TITLE'));
        $this->context->smarty->assign('description', Configuration::get('MC2P_DESCRIPTION'));
        $this->context->smarty->assign('icon', Configuration::get('MC2P_ICON'));

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * Build and display confirmation
     *
     * @param array $params
     * @return string Templatepart
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->isPayment()) {
            return false;
        }

        $this->context->smarty->assign('path', $this->_path);

        /* If PS version is >= 1.7 */
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->smarty->assign(array(
                'amount' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                )
            ));
        } else {
            $this->context->smarty->assign(array(
                'amount' => Tools::displayPrice(
                    $params['total_to_pay'],
                    $params['currencyObj'],
                    false
                )
            ));
        }

        $this->context->smarty->assign('shop_name', $this->context->shop->name);

        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
    }

    /**
     * Check if payment is active
     *
     * @return boolean
     */
    public function isPayment()
    {
        if (!$this->active) {
            return false;
        }

        if (!Configuration::get('MC2P_KEY')
            || !Configuration::get('MC2P_SECRET_KEY')
            || !Configuration::get('MC2P_TITLE')
            || !Configuration::get('MC2P_DESCRIPTION')) {
            return false;
        }

        return true;
    }
}
