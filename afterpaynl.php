<?php
/**
*   AfterPay Netherlands
*
*   Do not copy, modify or distribute this document in any form.
*
*   @author     Matthijs <matthijs@blauwfruit.nl>
*   @copyright  Copyright (c) 2013-2021 blauwfruit (https://blauwfruit.nl)
*   @license    Proprietary Software
*
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Afterpaynl extends PaymentModule
{
    public $notEligibleReason = array();

    public function __construct()
    {
        $this->name = 'afterpaynl';
        $this->tab = 'payments_gateways';
        $this->version = '3.2.3';
        $this->author = 'blauwfruit';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '409341bee0baf049a52faff643b4f905';
        $this->controllers = array('pay');
        parent::__construct();
        $this->displayName = $this->l('Afterpay NL');
        $this->description = $this->l('Pay directly with Afterpay.');
        $this->limited_countries = array('NL', 'BE');
        $this->limited_currencies = array('EUR', 'EU', 'EURO');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        
        $sql = "SELECT * FROM "._DB_PREFIX_."required_field WHERE object_name = 'Address' AND field_name = 'phone'";
        if (!Db::getInstance()->getRow($sql)) {
            Db::getInstance()->insert('required_field', array(
                'object_name' => 'Address',
                'field_name' => 'phone',
            ));
        }

        $sql = "SELECT * FROM "._DB_PREFIX_."required_field WHERE object_name = 'CustomerAddress' AND field_name = 'phone'";
        if (!Db::getInstance()->getRow($sql)) {
            Db::getInstance()->insert('required_field', array(
                'object_name' => 'CustomerAddress',
                'field_name' => 'phone',
            ));
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayPayment');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach (array('AFTERPAYNL_MODE', 'AFTERPAYNL_MERCHANT_ID_B2C', 'AFTERPAYNL_PORTOFOLIO_ID_B2C',
                'AFTERPAYNL_PASSWORD_B2C', 'AFTERPAYNL_B2C_MAXIMUM_FIRST_AMOUNT',
                'AFTERPAYNL_B2C_MAXIMUM_SECOND_AMOUNT',
                'AFTERPAYNL_MERCHANT_ID_B2B', 'AFTERPAYNL_PORTOFOLIO_ID_B2B',
                'AFTERPAYNL_PASSWORD_B2B', 'AFTERPAYNL_B2B_MAXIMUM_FIRST_AMOUNT',
                'AFTERPAYNL_B2B_MAXIMUM_SECOND_AMOUNT',

                'AFTERPAYNL_MERCHANT_ID_B2C_BE', 'AFTERPAYNL_PORTOFOLIO_ID_B2C_BE',
                'AFTERPAYNL_PASSWORD_B2C_BE', 'AFTERPAYNL_B2C_BE_MAXIMUM_FIRST_AMOUNT',
                'AFTERPAYNL_B2C_BE_MAXIMUM_SECOND_AMOUNT'



                ) as $ConfigName) {
                    Configuration::updateValue($ConfigName, Tools::getValue($ConfigName));
            }
        }
        $html .= $this->renderForm();
        $this->context->smarty->assign(array(
            'token' => sha1(_PS_BASE_URL_),
            'shop_url' => _PS_BASE_URL_.__PS_BASE_URI__,
            'context' => $this->context
        ));
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $html.$output;
    }


    /**
     * Create the structure of your form
     */
    public function renderForm()
    {
        $mode = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Mode'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'name' => 'AFTERPAYNL_MODE',
                        'label' => $this->l('Live?'),
                        'desc' => $this->l('Turn on if you want to push it live'),
                        'values' => array(
                            array(
                                'id' => 'on',
                                'value' => 1,
                                'name' => $this->l('On'),
                            ),
                            array(
                                'id' => 'off',
                                'value' => 0,
                                'name' => $this->l('Off'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'),
            )
        );
        $B2C = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Business to Consumer settings'),
                    'icon' => 'icon-cogs'
               ),
                'input' => array(
                    array(
                      'type'     => 'text',
                      'label'    => $this->l('Merchant ID'),
                      'name'     => 'AFTERPAYNL_MERCHANT_ID_B2C',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('2017000000')
                   ),
                    array(
                      'type'     => 'text',
                      'label'    => $this->l('Portofolio ID'),
                      'name'     => 'AFTERPAYNL_PORTOFOLIO_ID_B2C',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 1')
                   ),
                    array(
                      'type'     => 'text',
                      'label'    => $this->l('Password'),
                      'name'     => 'AFTERPAYNL_PASSWORD_B2C',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 8aka4a094b (leave empty to disable)')
                   ),
               ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'),
           ),
        );
        $B2B = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Business to Business settings'),
                    'icon' => 'icon-cogs',
                    'descr' => 'Hello',
               ),
                'input' => array(
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Merchant ID',
                      'label'    => $this->l('Merchant ID'),
                      'name'     => 'AFTERPAYNL_MERCHANT_ID_B2B',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('2017000000')
                   ),
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Portofolio ID',
                      'label'    => $this->l('Portofolio ID'),
                      'name'     => 'AFTERPAYNL_PORTOFOLIO_ID_B2B',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 1')
                   ),
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Password',
                      'label'    => $this->l('Password'),
                      'name'     => 'AFTERPAYNL_PASSWORD_B2B',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 8aka4a094b (leave empty to disable)')
                   ),
               ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right')
           ),
        );
        $B2C_BE = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Business to Consumer for Belgium settings'),
                    'icon' => 'icon-cogs',
                    'descr' => 'Hello',
               ),
                'input' => array(
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Merchant ID',
                      'label'    => $this->l('Merchant ID'),
                      'name'     => 'AFTERPAYNL_MERCHANT_ID_B2C_BE',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('2017000000')
                   ),
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Portofolio ID',
                      'label'    => $this->l('Portofolio ID'),
                      'name'     => 'AFTERPAYNL_PORTOFOLIO_ID_B2C_BE',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 1')
                   ),
                    array(
                      'type'     => 'text',
                      'placeholder' => 'Password',
                      'label'    => $this->l('Password'),
                      'name'     => 'AFTERPAYNL_PASSWORD_B2C_BE',
                      'size'     => 50,
                      'required' => true,
                      'desc'     => $this->l('Example: 8aka4a094b (leave empty to disable)')
                   ),
               ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right')
           ),
        );


        $helper = new HelperForm();
        $helper->submit_action = 'submit'.$this->name;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;
        # B2C
        $helper->fields_value['AFTERPAYNL_MODE'] = Configuration::get('AFTERPAYNL_MODE');
        $helper->fields_value['AFTERPAYNL_MERCHANT_ID_B2C'] = Configuration::get('AFTERPAYNL_MERCHANT_ID_B2C');
        $helper->fields_value['AFTERPAYNL_PORTOFOLIO_ID_B2C'] = Configuration::get('AFTERPAYNL_PORTOFOLIO_ID_B2C');
        $helper->fields_value['AFTERPAYNL_PASSWORD_B2C'] = Configuration::get('AFTERPAYNL_PASSWORD_B2C');
        $AFTERPAYNL_B2C_MAXIMUM_FIRST_AMOUNT = Configuration::get('AFTERPAYNL_B2C_MAXIMUM_FIRST_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2C_MAXIMUM_FIRST_AMOUNT'] = $AFTERPAYNL_B2C_MAXIMUM_FIRST_AMOUNT;
        $AFTERPAYNL_B2C_MAXIMUM_SECOND_AMOUNT = Configuration::get('AFTERPAYNL_B2C_MAXIMUM_SECOND_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2C_MAXIMUM_SECOND_AMOUNT'] = $AFTERPAYNL_B2C_MAXIMUM_SECOND_AMOUNT;
        # B2B
        $helper->fields_value['AFTERPAYNL_MERCHANT_ID_B2B'] = Configuration::get('AFTERPAYNL_MERCHANT_ID_B2B');
        $helper->fields_value['AFTERPAYNL_PORTOFOLIO_ID_B2B'] = Configuration::get('AFTERPAYNL_PORTOFOLIO_ID_B2B');
        $helper->fields_value['AFTERPAYNL_PASSWORD_B2B'] = Configuration::get('AFTERPAYNL_PASSWORD_B2B');
        $AFTERPAYNL_B2B_MAXIMUM_FIRST_AMOUNT = Configuration::get('AFTERPAYNL_B2B_MAXIMUM_FIRST_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2B_MAXIMUM_FIRST_AMOUNT'] = $AFTERPAYNL_B2B_MAXIMUM_FIRST_AMOUNT;
        $AFTERPAYNL_B2B_MAXIMUM_SECOND_AMOUNT = Configuration::get('AFTERPAYNL_B2B_MAXIMUM_SECOND_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2B_MAXIMUM_SECOND_AMOUNT'] = $AFTERPAYNL_B2B_MAXIMUM_SECOND_AMOUNT;
        # B2C BE
        $helper->fields_value['AFTERPAYNL_MERCHANT_ID_B2C_BE'] = Configuration::get('AFTERPAYNL_MERCHANT_ID_B2C_BE');
        $AFTERPAYNL_PORTOFOLIO_ID_B2C_BE = Configuration::get('AFTERPAYNL_PORTOFOLIO_ID_B2C_BE');
        $helper->fields_value['AFTERPAYNL_PORTOFOLIO_ID_B2C_BE'] = $AFTERPAYNL_PORTOFOLIO_ID_B2C_BE;
        $helper->fields_value['AFTERPAYNL_PASSWORD_B2C_BE'] = Configuration::get('AFTERPAYNL_PASSWORD_B2C_BE');
        $AFTERPAYNL_B2C_BE_MAXIMUM_FIRST_AMOUNT = Configuration::get('AFTERPAYNL_B2C_BE_MAXIMUM_FIRST_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2C_BE_MAXIMUM_FIRST_AMOUNT'] = $AFTERPAYNL_B2C_BE_MAXIMUM_FIRST_AMOUNT;
        $AFTERPAYNL_B2C_BE_MAXIMUM_SECOND_AMOUNT = Configuration::get('AFTERPAYNL_B2C_BE_MAXIMUM_SECOND_AMOUNT');
        $helper->fields_value['AFTERPAYNL_B2C_BE_MAXIMUM_SECOND_AMOUNT'] = $AFTERPAYNL_B2C_BE_MAXIMUM_SECOND_AMOUNT;

        return $helper->generateForm(array($mode,$B2C,$B2B,$B2C_BE));
    }
    
    /**
     * Some styles to be loaded here
     */
    public function hookHeader()
    {
    }

    public function isEligible($companyName, $isoCode)
    {
        if ($companyName == '' && $isoCode == 'NL' && Configuration::get('AFTERPAYNL_PASSWORD_B2C') == '') {
            $this->notEligibleReason[] = $this->l('AfterPay is not available in your situation because you are a consumer residing in the Netherlands. Change the invoice and/or delivery address');
            return false;
        } elseif ($companyName !== '' && $isoCode == 'NL' && Configuration::get('AFTERPAYNL_PASSWORD_B2B') == '') {
            $this->notEligibleReason[] = $this->l('AfterPay is not available in your situation because you are a business residing in the Netherlands. Change the invoice and/or delivery address');
            return false;
        } elseif ($companyName == '' && $isoCode == 'BE' && Configuration::get('AFTERPAYNL_PASSWORD_B2C_BE') == '') {
            $this->notEligibleReason[] = $this->l('AfterPay is not available in your situation because you are a consumer residing in Belgium. Change the invoice and/or delivery address');
            return false;
        } elseif ($companyName !== '' && $isoCode == 'BE') {
            $this->notEligibleReason[] = $this->l('AfterPay is not available in your situation because you are a business residing in Belgium. Change the invoice and/or delivery address');
            return false;
        }

        return true;
    }

    public function getNotEligibleReason()
    {
        return $this->notEligibleReason;
    }

    /**
     * Return AfterPay in the order confirmation
     * @param (object) $params (order, etc.)
     * @return Display confirmation.tpl
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if ((int) Tools::getValue('id_module') !== (int) $this->id) {
            return;
        }

        if ($this->isPS17()) {
            $this->smarty->assign('reference', $params['order']->reference);
            return $this->context->smarty->fetch('module:afterpaynl/views/templates/hook/confirmation.tpl');
        } else {
            $this->smarty->assign('reference', $params['objOrder']->reference);
            return $this->display($this->_path, 'views/templates/hook/confirmation.tpl');
        }
    }

    /**
     * Payment options for 1.7
     * @return (array) payment options
     */
    public function hookPaymentOptions($params)
    {
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay with AfterPay', array(), 'Modules.AfterPayNL.Front'))
                ->setAction($this->context->link->getModuleLink($this->name, 'pay', array()))
                ->setAdditionalInformation($this->fetch('module:afterpaynl/views/templates/front/payment_infos.tpl'))
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/Logo-afterpay-checkout_L.png'));

        return array($newOption);
    }

    /**
     * Return AfterPay in the payment options for 1.6
     * @param (object) $params (cart, etc.)
     * @return payment.tpl
     */
    public function hookDisplayPayment($params)
    {
        $address_delivery = new Address((int) $params['cart']->id_customer);
        $country_delivery = new Country((int) $address_delivery->id_country);

        if (!$this->isEligible($address_delivery->company, $country_delivery->iso_code)) {
            return;
        }

        $this->smarty->assign(array(
            'module_dir' => $this->_path,
            'payment_url' => $this->context->link->getModuleLink('afterpaynl', 'pay', array(), true)
        ));
        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }    

    /**
     *  Payment return
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['objOrder']->getCurrentState();
        if (in_array($state, array(
            Configuration::get('PS_OS_CHEQUE'),
            Configuration::get('PS_OS_OUTOFSTOCK'),
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
                'shop_name' => $this->context->shop->name,
                'checkName' => $this->checkName,
                'checkAddress' => Tools::nl2br($this->address),
                'status' => 'ok',
                'id_order' => $params['order']->id
            ));
            if (isset($params['order']->reference) && !empty($params['order']->reference)) {
                $this->smarty->assign('reference', $params['order']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }
        if ($this->isPS17()) {
            $this->display('module:afterpaynl/views/templates/hook/payment_return.tpl');
        } else {
            return $this->display($this->_path, 'views/templates/hook/payment_return.tpl');
        }
    }

    /**
     *  Is it PrestaShop 1.7?
     *  @return  bool 
     */
    public function isPS17()
    {
        return version_compare(_PS_VERSION_, '1.7', '>=');
    }
}
