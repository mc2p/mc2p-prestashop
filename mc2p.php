<?php
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
$autoloader_param = __DIR__ . '/lib/MC2P/MC2PClient.php';
// Load up the MC2P library
try {
    require_once $autoloader_param;
} catch (\Exception $e) {
    throw new \Exception('The MC2P payment plugin was not installed correctly or the files are corrupt. Please reinstall the plugin. If this message persists after a reinstall, contact hola@mychoice2pay.com with this message.');
}

if (!defined('_CAN_LOAD_FILES_')) {
	exit;
}

class MC2P extends PaymentModule
{
	private	$html = '';
	private $post_errors = array();

	public function __construct()
	{
		$this->name = 'mc2p';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'MyChoice2Pay';

		// Array config with configuration data
		$config = Configuration::getMultiple(array('MC2P_KEY', 'MC2P_SECRET_KEY', 'MC2P_DISPLAY_NAME', 'MC2P_DESCRIPTION'));

		if (isset($config['MC2P_KEY'])) {
			$this->key = $config['MC2P_KEY'];
        }
		if (isset($config['MC2P_SECRET_KEY'])) {
			$this->secretKey = $config['MC2P_SECRET_KEY'];
        }
		if (isset($config['MC2P_DISPLAY_NAME'])) {
			$this->displayName = $config['MC2P_DISPLAY_NAME'];
        } else {
            $this->displayName = $this->l('MyChoice2Pay');
        }
		if (isset($config['MC2P_DESCRIPTION'])) {
			$this->description = $config['MC2P_DESCRIPTION'];
        } else {
            $this->description = $this->l('Select among several payment methods the one that works best for you in MyChoice2Pay');
        }

		parent::__construct();

		$this->page = basename(__FILE__, '.php');

		// Show warning if key or secret key is missing
		if (!isset($this->key) || !isset($this->secretKey)) {

		    $this->warning = $this->l('Key and Secret Key are missing');
		}
	}

	public function install()
	{
		// Default values when install
		if (!parent::install()
			|| !Configuration::updateValue('MC2P_KEY', '')
			|| !Configuration::updateValue('MC2P_SECRET_KEY', '')
			|| !Configuration::updateValue('MC2P_DISPLAY_NAME', $this->l('MyChoice2Pay'))
			|| !Configuration::updateValue('MC2P_DESCRIPTION', $this->l('Select among several payment methods the one that works best for you in MyChoice2Pay'))
			|| !$this->registerHook('paymentOptions')
			|| !$this->registerHook('paymentReturn')) {
			return false;
        }
		return true;
	}

	public function uninstall()
	{
		// Remove values when uninstall
		if (!Configuration::deleteByName('MC2P_KEY')
			|| !Configuration::deleteByName('MC2P_SECRET_KEY')
			|| !Configuration::deleteByName('MC2P_DISPLAY_NAME')
			|| !Configuration::deleteByName('MC2P_DESCRIPTION')
			|| !parent::uninstall()) {
			return false;
        }
		return true;
	}

	private function _postValidation()
	{
		// Show error if missing values
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('key')) {
				$this->post_errors[] = $this->l('Key missing');
            }
			if (!Tools::getValue('secretKey')) {
				$this->post_errors[] = $this->l('Secret Key missing');
            }
		}
	}

	private function _postProcess()
	{
		// Update config of DB
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('MC2P_KEY', Tools::getValue('key'));
			Configuration::updateValue('MC2P_SECRET_KEY', Tools::getValue('secretKey'));
			Configuration::updateValue('MC2P_DISPLAY_NAME', Tools::getValue('displayName'));
			Configuration::updateValue('MC2P_DESCRIPTION', Tools::getValue('description'));
		}
		$this->html .= $this->displayConfirmation($this->l('Configuration updated'));
	}

	
	private function _displayMC2P()
	{
		// payment list
		$this->html .= '<img src="../modules/mc2p/assets/images/icons/mc2p.png" style="float:left; margin-right:15px;"><b><br />'
		.$this->l('Allows to receive payments from several payment gateways while offering the possibility of dividing payments between several people.').'</b><br />';
	}

	private function _displayForm()
	{
		$key = Tools::getValue('key', $this->key);
		$secretKey = Tools::getValue('secretKey', $this->secretKey);
		$displayName = Tools::getValue('displayName', $this->displayName);
		$description = Tools::getValue('description', $this->description);

		// Show form
		$this->html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			    <legend>'.$this->l('MC2P Configuration').'</legend>
				<table border="0" width="100%" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Complete configuration data').'.<br /><br /></td></tr>
					<tr><td width="30%" style="height: 35px;">'.$this->l('Key').'</td><td><input name="key" type="text" required value"'.$key.'"></td></tr>
					<tr><td width="30%" style="height: 35px;">'.$this->l('Secret Key').'</td><td><input name="secretKey" required type="text" value"'.$secretKey.'"></td></tr>
					<tr><td width="30%" style="height: 35px;">'.$this->l('Display Name').'</td><td><input name="displayName" type="text" value"'.$displayName.'"></td></tr>
					<tr><td width="30%" style="height: 35px;">'.$this->l('Description').'</td><td><input name="description" type="text" value"'.$description.'"></td></tr>
				</table>
			</fieldset>
			<br>
		    <input class="button" name="btnSubmit" value="'.$this->l('Save configuration').'" type="submit" />
		</form>';
	}

	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->post_errors)) {
				$this->_postProcess();
            } else {
				foreach ($this->post_errors as $err) {
					$this->html .= $this->displayError($err);
                }
            }
		} else {
			$this->html .= '<br />';
        }
		$this->_displayMC2P();
		$this->_displayForm();
		return $this->html;
	}

	public function hookPaymentOptions($params)
	{
		if (!$this->active)
			return;

		// Value  of purchase
		$currency = new Currency($params['cart']->id_currency);
		$amount = $params['cart']->getOrderTotal(true, Cart::BOTH);

		// Order number
		$orderId = $params['cart']->id;

		// URLs
		$customer = new Customer($params['cart']->id_customer);
		$idCart = (int)$params['cart']->id;
		if (empty($_SERVER['HTTPS'])) {
		    $startUrl = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
		} else {
			$startUrl = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
		}
		$notifyUrl = $startUrl.'modules/mc2p/notify.php';
		$returnUrl = $startUrl.'index.php?controller=order-confirmation&id_cart='.$idCart.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key;
		$cancelUrl = $startUrl.'pedido';

		$webLanguage = Tools::substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		switch ($webLanguage) {
            case 'es':
                $language = 'es';
                break;
            case 'en':
                $language = 'en';
                break;
            default:
                $language = 'au';
                break;
        }

		// Create transaction
		$mc2p = new MC2P\MC2PClient($this->key, $this->secretKey);
        $transaction = $mc2p->Transaction(
            array(
                "order_id" => $orderId,
                "currency" => $currency,
                "return_url"  => $returnUrl,
                "cancel_url" => $cancelUrl,
                "notify_url" => $notifyUrl,
                "language" => $language,
                "products" => array(
                    array(
                        "amount" => 1,
                        "product" => array(
                            "name" => __('Payment of order ', 'wc-gateway-mc2p').$order_id,
                            "price" => $order->get_total()
                        )
                    )
                )
            )
        );
        $transaction->save();
        $payUrl = $transaction->getPayUrl();

        $this->smarty->assign(array(
			'payUrl' => $payUrl,
			'this_path' => $this->_path
		));

		// Form
        $formMC2P = '<form id="payment-form" method="POST" action="'.$payUrl.'"></form>';
		$newOption = new PaymentOption();
		$newOption->setCallToActionText($this->trans('Pay with MyChoice2Pay', array(), 'Modules.MC2P.Shop'))
		    ->setLogo(_MODULE_DIR_.'mc2p/assets/images/icons/mc2p.png')
		    ->setAdditionalInformation($this->fetch('module:redsys/views/templates/hook/payment.tpl'))
		    ->setForm($formMC2P)
		    ->setAction($this->$payUrl);
		$payment_options = [
            $newOption,
        ];
        return $payment_options;
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$this->smarty->assign(array(
			'shop_name' => $this->context->shop->name,
			'totalPay' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
			'status' => 'ok',
			'orderId' => $params['order']->reference,
			'this_path' => $this->_path
		));

		return $this->display(__FILE__, 'payment_return.tpl');
	}
}
?>