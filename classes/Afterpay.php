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

class Afterpay
{
    public $authorization;
    public $modus;
    public $order;
    public $order_lines = array();
    public $order_type;
    public $order_type_name;
    public $order_type_function;
    public $order_request;
    public $order_result;
    public $soap_client;
    public $total_order_amount = 0;
    public $wsdl;
    public $country = 'NL';
    public $ordermanagement = false;
    public $orderaction = null;
    public function __construct()
    {
        $this->order = new \stdClass();
        $this->order->shopper = new \stdClass();
    }
   
    /**
     * If order management is used set action to true;
     *
     * @param boolean       $action
     */
    public function setOrdermanagement($action)
    {
        $this->ordermanagement = true;
        $this->orderaction = $action;
    }
   
    /**
     * Create order information
     *
     * @param array         $order
     * @param string        $order_type
     */
    public function setOrder($order, $order_type)
    {
        // Set order_type, options are B2C, B2B, OM
        $this->setOrderType($order_type);
       
        if ($this->order_type == 'OM') {
            switch ($this->orderaction) {
                case 'capture_full':
                    $this->order->invoicenumber = $order['invoicenumber'];
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    $this->order->capturedelaydays = 0;
                    $this->order->shippingCompany = '';
                    break;
                case 'capture_partial':
                    $this->order->invoicelines = $this->order_lines;
                    $this->order->invoicenumber = $order['invoicenumber'];
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    $this->order->capturedelaydays = 0;
                    $this->order->shippingCompany = '';
                    break;
                case 'cancel':
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    break;
                case 'status':
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    break;
                case 'refund_full':
                    $this->order->invoicenumber = $order['invoicenumber'];
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    $this->order->creditInvoicenNumber = $order['creditinvoicenumber'];
                    break;
                case 'refund_partial':
                    $this->order->invoicelines = $this->order_lines;
                    $this->order->invoicenumber = $order['invoicenumber'];
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    $this->order->creditInvoicenNumber = $order['creditinvoicenumber'];
                    break;
                case 'void':
                    $this->order->transactionkey = new \stdClass();
                    $this->order->transactionkey->ordernumber = $order['ordernumber'];
                    break;
                default:
                    break;
            }
            return;
        }
       
        if ($this->order_type == 'B2C') {
            $billto_address = 'b2cbilltoAddress';
            $shipto_address = 'b2cshiptoAddress';
        } elseif ($this->order_type == 'B2B') {
            $billto_address = 'b2bbilltoAddress';
            $shipto_address = 'b2bshiptoAddress';
        }
       
        $this->country = 'NL';
       
        if ($order['billtoaddress']['isocountrycode'] == 'BE') {
            $this->country = 'BE';
        } elseif ($order['billtoaddress']['isocountrycode'] == 'DE') {
            $this->country = 'DE';
        }
       
        $this->order->$billto_address = new \stdClass();
        $this->order->$shipto_address = new \stdClass();

        if ($this->order_type == 'B2C') {
            $this->order->$billto_address->referencePerson = new \stdClass();
            $this->order->$shipto_address->referencePerson = new \stdClass();
        }
        $this->order->$billto_address->city = $order['billtoaddress']['city'];
        $this->order->$billto_address->housenumber = $order['billtoaddress']['housenumber'];
        $this->order->$billto_address->housenumberAddition = '';
        if (array_key_exists('housenumberaddition', $order['billtoaddress'])) {
            $this->order->$billto_address->housenumberAddition = $order['billtoaddress']['housenumberaddition'];
        }
        $this->order->$billto_address->isoCountryCode = $order['billtoaddress']['isocountrycode'];
        $this->order->$billto_address->postalcode = $order['billtoaddress']['postalcode'];
        $this->order->$billto_address->streetname = $order['billtoaddress']['streetname'];
        $this->order->$shipto_address->city = $order['shiptoaddress']['city'];
        $this->order->$shipto_address->housenumber = $order['shiptoaddress']['housenumber'];
        $this->order->$shipto_address->housenumberAddition = '';
        if (array_key_exists('housenumberaddition', $order['shiptoaddress'])) {
            $this->order->$shipto_address->housenumberAddition = $order['shiptoaddress']['housenumberaddition'];
        }
        $this->order->$shipto_address->isoCountryCode = $order['shiptoaddress']['isocountrycode'];
        $this->order->$shipto_address->postalcode = $order['shiptoaddress']['postalcode'];
        $this->order->$shipto_address->streetname = $order['shiptoaddress']['streetname'];
        if ($this->order_type == 'B2C') {
            $this->order->$billto_address->referencePerson
                        ->dateofbirth = $order['billtoaddress']['referenceperson']['dob'];
            $this->order->$billto_address->referencePerson
                        ->emailaddress = $order['billtoaddress']['referenceperson']['email'];
            $this->order->$billto_address->referencePerson
                        ->gender = $order['billtoaddress']['referenceperson']['gender'];
            $this->order->$billto_address->referencePerson
                        ->initials = $order['billtoaddress']['referenceperson']['initials'];
            $this->order->$billto_address->referencePerson
                        ->isoLanguage = $order['billtoaddress']['referenceperson']['isolanguage'];
            $this->order->$billto_address->referencePerson
                        ->lastname = $order['billtoaddress']['referenceperson']['lastname'];
            $this->order->$billto_address->referencePerson
                        ->phonenumber1 = $this->cleanphone(
                            $order['billtoaddress']['referenceperson']['phonenumber'],
                            $order['billtoaddress']['isocountrycode']
                        );
            $this->order->$shipto_address->referencePerson
                        ->dateofbirth = $order['shiptoaddress']['referenceperson']['dob'];
            $this->order->$shipto_address->referencePerson
                        ->emailaddress = $order['shiptoaddress']['referenceperson']['email'];
            $this->order->$shipto_address->referencePerson
                        ->gender = $order['shiptoaddress']['referenceperson']['gender'];
            $this->order->$shipto_address->referencePerson
                        ->initials = $order['shiptoaddress']['referenceperson']['initials'];
            $this->order->$shipto_address->referencePerson
                        ->isoLanguage = $order['shiptoaddress']['referenceperson']['isolanguage'];
            $this->order->$shipto_address->referencePerson
                        ->lastname = $order['shiptoaddress']['referenceperson']['lastname'];
            $this->order->$shipto_address->referencePerson
                        ->phonenumber1 = $this->cleanphone(
                            $order['shiptoaddress']['referenceperson']['phonenumber'],
                            $order['billtoaddress']['isocountrycode']
                        );
        }
        if ($this->order_type == 'B2B') {
            if (!isset($this->order->company)) {
                $this->order->company = (object)array();
            }
            $this->order->company->cocnumber = $order['company']['cocnumber'];
            $this->order->company->companyname = $order['company']['companyname'];
            $this->order->company->vatnumber = '';
            if (!isset($this->order->person)) {
                $this->order->person = (object)array();
            }
            $this->order->person->dateofbirth = $order['billtoaddress']['referenceperson']['dob'];
            $this->order->person->emailaddress = $order['billtoaddress']['referenceperson']['email'];
            $this->order->person->gender = '';
            $this->order->person->initials = $order['billtoaddress']['referenceperson']['initials'];
            $this->order->person->isoLanguage = $order['billtoaddress']['referenceperson']['isolanguage'];
            $this->order->person->lastname = $order['billtoaddress']['referenceperson']['lastname'];
            $this->order->person->phonenumber1 = $this->cleanphone(
                $order['billtoaddress']['referenceperson']['phonenumber'],
                $order['billtoaddress']['isocountrycode']
            );
        }
        
        $this->order->ordernumber = $order['ordernumber'];
        $this->order->bankaccountNumber = (isset($order['bankaccountnumber'])) ? $order['bankaccountnumber'] : '';
        $this->order->currency = $order['currency'];
        $this->order->ipAddress = $order['ipaddress'];
        $this->order->shopper->profilecreated = '2013-01-01T00:00:00';
        $this->order->parentTransactionreference = false;
        $this->order->orderlines = $this->order_lines;
        $this->order->totalOrderAmount =  $this->total_order_amount;
    }

    /**
     * Function to create order lines
     *
     * @param string        $id
     * @param string        $description
     * @param int           $quantity
     * @param int           $unit_price
     * @param int           $vat_category
     *
     */
    public function createOrderLine($product_id, $description, $quantity, $unit_price, $vat_category)
    {
        $order_line = new \stdClass();
        $order_line->articleId = $product_id;
        $order_line->articleDescription = $description;
        $order_line->quantity = $quantity;
        $order_line->unitprice = (string)$unit_price;
        $order_line->vatcategory = $vat_category;
        $this->total_order_amount =  (string)($this->total_order_amount + ($quantity * $unit_price));
        $this->order_lines[] = $order_line;
    }
    /**
     * Process request to SOAP webservice
     *
     * @param array         $authorization
     * @param string        $modus
     *
     */
    public function doRequest($authorization, $modus)
    {
        $this->setModus($modus);
        $this->setSoapClient();
        $this->setAuthorization($authorization);
        try {
            $this->order_result = $this->soap_client->__soapCall(
                $this->order_type_name,
                array(
                    $this->order_type_name => array(
                        'authorization' => $this->authorization,
                        $this->order_type_function => $this->order
                    )
                )
            );
            $return_message = array();
            if (isset($this->order_result->return->resultId) && $this->order_result->return->resultId == 2) {
                if (is_array($this->order_result->return->failures)) {
                    foreach ($this->order_result->return->failures as $failure) {
                        $validation_error = $this->checkValidationError($failure->failure, $failure->fieldname);
                        $return_message[] = array('message' => $validation_error, 'description'=>$validation_error);
                    }
                } else {
                    $validation_error = $this->checkValidationError(
                        $this->order_result->return->failures->failure,
                        $this->order_result->return->failures->fieldname
                    );
                    $return_message[] = array('message' => $validation_error, 'description'=>$validation_error);
                }
                $this->order_result->return->messages = new \stdClass;
                $this->order_result->return->messages = $return_message;
            } elseif (isset($this->order_result->return->resultId) && $this->order_result->return->resultId == 3) {
                if (isset($this->order_result->return->rejectCode)) {
                    $rejection_error = $this->checkRejectionError($this->order_result->return->rejectCode);
                } else {
                    $rejection_error = $this->checkRejectionError(0);
                }
                $this->order_result->return->messages = new \stdClass;
                $this->order_result->return->messages = array($rejection_error);
            }
        } catch (\Exception $e) {
            $this->order_result = new \stdClass;
            $this->order_result->return = new \stdClass;
            $this->order_result->return->failures = new \stdClass;
            $this->order_result->return->failures->failure = $e->faultstring;
            $this->order_result->return->failures
                ->description = "A technical error occured, please contact the webshop.";
            $this->order_result->return->messages = array($this->checkTechnicalError($e->faultstring));
            $this->order_result->return->resultId = 1;
        }
    }

    /**
     * Set order types to correct webservice calls and function names
     *
     * @param string        $order_type
     *
     */
    private function setOrderType($order_type)
    {
        if (!$this->ordermanagement) {
            switch ($order_type) {
                case 'B2C':
                    $this->order_type = 'B2C';
                    $this->order_type_name = 'validateAndCheckB2COrder';
                    $this->order_type_function = 'b2corder';
                    break;
                case 'B2B':
                    $this->order_type = 'B2B';
                    $this->order_type_name = 'validateAndCheckB2BOrder';
                    $this->order_type_function = 'b2border';
                    break;
                default:
                    break;
            }
        } else {
            switch ($this->orderaction) {
                case 'capture_full':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'captureFull';
                    $this->order_type_function = 'captureobject';
                    break;
                case 'capture_partial':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'capturePartial';
                    $this->order_type_function = 'captureobject';
                    break;
                case 'cancel':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'cancelOrder';
                    $this->order_type_function = 'ordermanagementobject';
                    break;
                case 'status':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'requestOrderStatus';
                    $this->order_type_function = 'ordermanagementobject';
                    break;
                case 'refund_full':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'refundFullInvoice';
                    $this->order_type_function = 'refundobject';
                    break;
                case 'refund_partial':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'refundInvoice';
                    $this->order_type_function = 'refundobject';
                    break;
                case 'void':
                    $this->order_type = 'OM';
                    $this->order_type_name = 'doVoid';
                    $this->order_type_function = 'ordermanagementobject';
                    break;
            }
        }
    }

    /**
     * Set modus, options are test or live
     *
     * @param string        $modus
     *
     */
    private function setModus($modus)
    {
        $this->modus = $modus;
        $this->wsdl = $this->getWSDL($this->country, $modus);
    }

    /**
     * Get correct WSDL endpoint
     *
     * @param string        $country
     * @param string        $modus
     *
     * @return string       $wsdl
     */
    private function getWSDL($country, $modus)
    {
        if (!$this->ordermanagement) {
            if ($country == 'NL') {
                if ($modus == 'test') {
                    $wsdl = 'https://test.acceptgirodienst.nl/soapservices/rm/AfterPaycheck?wsdl';
                } elseif ($modus == 'live') {
                    $wsdl = 'https://www.acceptgirodienst.nl/soapservices/rm/AfterPaycheck?wsdl';
                }
            } elseif ($country == 'BE') {
                if ($modus == 'test') {
                    $wsdl = 'https://test.afterpay.be/soapservices/rm/AfterPaycheck?wsdl';
                } elseif ($modus == 'live') {
                    $wsdl = 'https://api.afterpay.be/soapservices/rm/AfterPaycheck?wsdl';
                }
            }
        } else {
            if ($country == 'NL') {
                if ($modus == 'test') {
                    $wsdl = 'https://test.acceptgirodienst.nl/soapservices/om/OrderManagement?wsdl';
                } elseif ($modus == 'live') {
                    $wsdl = 'https://www.acceptgirodienst.nl/soapservices/om/OrderManagement?wsdl';
                }
            } elseif ($country == 'BE') {
                if ($modus == 'test') {
                    $wsdl = 'https://test.afterpay.be/soapservices/om/OrderManagement?wsdl';
                } elseif ($modus == 'live') {
                    $wsdl = 'https://api.afterpay.be/soapservices/om/OrderManagement?wsdl';
                }
            }
        }

        return $wsdl;
    }

    /**
     * Set correct soap client, dif fers per country
     *
     */
    private function setSoapClient()
    {
        if ($this->country == 'NL') {
            $this->soap_client = new \SoapClient(
                $this->wsdl,
                array(
                    'trace' => 0,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'exceptions' => true
                )
            );
        } elseif ($this->country == 'BE') {
            $this->soap_client = new \SoapClient(
                $this->wsdl,
                array(
                    'location' => $this->wsdl,
                    'trace' => 0,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'exceptions' => true
                )
            );
        }
    }

    /**
     * Set authorisation credentials
     *
     * @param array         $authorization
     */
    private function setAuthorization($authorization)
    {
        $this->authorization = new \stdClass();
        $this->authorization->merchantId = $authorization['merchantid'];
        $this->authorization->portfolioId = $authorization['portfolioid'];
        $this->authorization->password = $authorization['password'];
    }

    /**
     * Function for cleaning phone numbers to correct data depending on country
     *
     * @param string        $phonenumber
     * @param string        $country
     *
     * @return string       $phonenumber
     */
    private function cleanphone($phonenumber, $country = 'NL')
    {
        // Replace + with 00
        $phonenumber = str_replace('+', '00', $phonenumber);
        // Remove (0) because output is international format
        $phonenumber = str_replace('(0)', '', $phonenumber);
        // Only numbers
        $phonenumber = preg_replace("/[^0-9]/", "", $phonenumber);
        // Country specif ic checks
        if ($country == 'NL') {
            if (Tools::strlen($phonenumber) == '10'
                && Tools::substr($phonenumber, 0, 3) != '0031'
                && Tools::substr($phonenumber, 0, 1) == '0') {
                $phonenumber = '0031' . Tools::substr($phonenumber, -9);
            } elseif (Tools::strlen($phonenumber) == '13' && Tools::substr($phonenumber, 0, 3) == '0031') {
                $phonenumber = '0031' . Tools::substr($phonenumber, -9);
            }
        } elseif ($country == 'BE') {
            // Land lines
            if (Tools::strlen($phonenumber) == '9'
                && Tools::substr($phonenumber, 0, 3) != '0032'
                && Tools::substr($phonenumber, 0, 1) == '0') {
                $phonenumber = '0032' . Tools::substr($phonenumber, -8);
            } elseif (Tools::strlen($phonenumber) == '12' && Tools::substr($phonenumber, 0, 3) == '0032') {
                $phonenumber = '0032' . Tools::substr($phonenumber, -8);
            }
            // Mobile lines
            if (Tools::strlen($phonenumber) == '10'
                && Tools::substr($phonenumber, 0, 3) != '0032'
                && Tools::substr($phonenumber, 0, 1) == '0') {
                $phonenumber = '0032' . Tools::substr($phonenumber, -9);
            } elseif (Tools::strlen($phonenumber) == '13' && Tools::substr($phonenumber, 0, 3) == '0032') {
                $phonenumber = '0032' . Tools::substr($phonenumber, -9);
            }
        }
        return $phonenumber;
    }

    /**
     * Check validation error and give back readable error message
     *
     * @param string        $failure
     * @param string        $fieldname
     * @param string        $language
     *
     * @return string
     */
    public function checkValidationError($failure, $fieldname, $language = 'nl')
    {
        // Belgium has a dif ferent buildup of the failure message
        if (in_array($failure, array('field.invalid', 'field.missing'))) {
            $oldFailure = explode('.', $failure);
            // In Belgium person is ReferencePerson, so replace
            $fieldname = str_replace('referencePerson', 'person', $fieldname);
            // In Belgium phonenumber1 is onder person, so replace
            $fieldname = str_replace('person.phonenumber1', 'phonenumber1', $fieldname);
            $fieldname = str_replace('person.phonenumber2', 'phonenumber2', $fieldname);
           
            $field_failure = $oldFailure[0] . '.' . $fieldname . '.' . $oldFailure[1];
        } else {
            $field_failure = $failure;
        }
       
        // Set language for field failure
        $field_failure = $language . '.' . $field_failure;
       
        switch ($field_failure) {
            case 'en.field.unknown.invalid':
                return 'An unknown field is invalid, please contact our customer service.';
            case 'en.field.shipto.person.initials.missing':
                return 'The initials of the shipping address are missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.initials.invalid':
                return 'The initials of the shipping address are invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.initials.missing':
                return 'The initials of the billing address are missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.initials.invalid':
                return 'The initials of the billing address are invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.person.lastname.missing':
                return 'The last name of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.lastname.invalid':
                return 'The last name of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.lastname.missing':
                return 'The last name of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.lastname.invalid':
                return 'The last name of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.city.missing':
                return 'The city of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.city.invalid':
                return 'The city of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.city.missing':
                return 'The city of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.city.invalid':
                return 'The city of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.housenumber.missing':
                return 'The house number of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.housenumber.invalid':
                return 'The house number of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.housenumber.missing':
                return 'The house number of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.housenumber.invalid':
                return 'The house number of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.postalcode.missing':
                return 'The postalcode of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.postalcode.invalid':
                return 'The postalcode of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.postalcode.missing':
                return 'The postalcode of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.postalcode.invalid':
                return 'The postalcode of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.gender.missing':
                return 'The gender of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.gender.invalid':
                return 'The gender of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.gender.missing':
                return 'The gender of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.gender.invalid':
                return 'The gender of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.housenumberaddition.missing':
                return 'The house number addition of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.housenumberaddition.invalid':
                return 'The house number addition of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.housenumberaddition.missing':
                return 'The house number addition of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.housenumberaddition.invalid':
                return 'The house number addition of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.phonenumber1.missing':
                return 'The fixed line and/or mobile number is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.phonenumber1.invalid':
                return 'The fixed line and/or mobile number is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.phonenumber2.invalid':
                return 'The fixed line and/or mobile number is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.person.emailaddress.missing':
                return 'The email address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.emailaddress.invalid':
                return 'The email address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.emailaddress.missing':
                return 'The email address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.emailaddress.invalid':
                return 'The email address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.person.dateofbirth.missing':
                return 'The date of birth is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.dateofbirth.invalid':
                return 'The date of birth is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.dateofbirth.missing':
                return 'The date of birth is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.dateofbirth.invalid':
                return 'The date of birth is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.isocountrycode.missing':
                return 'The country code of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.isocountrycode.invalid':
                return 'The country code of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.isocountrycode.missing':
                return 'The country code of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.isocountrycode.invalid':
                return 'The country code of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.prefix.missing':
                return 'The prefix of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.person.prefix.invalid':
                return 'The prefix of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.billto.person.prefix.missing':
                return 'The prefix of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.person.prefix.invalid':
                return 'The prefix of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.isolanguagecode.missing':
                return 'The language of the billing address is missing.
                    Please check your billing details or contact our customer service.';
            case 'en.field.billto.isolanguagecode.invalid':
                return 'The language of the billing address is invalid.
                    Please check your billing details or contact our customer service.';
            case 'en.field.shipto.isolanguagecode.missing':
                return 'The language of the shipping address is missing.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.shipto.isolanguagecode.invalid':
                return 'The language of the shipping address is invalid.
                    Please check your shipping details or contact our customer service.';
            case 'en.field.ordernumber.missing':
                return 'The ordernumber is missing.
                    Please contact our customer service.';
            case 'en.field.ordernumber.invalid':
                return 'The ordernumber is invalid.
                    Please contact our customer service.';
            case 'en.field.ordernumber.exists':
                return 'The ordernumber already exists.
                    Please contact our customer service.';
            case 'en.field.bankaccountnumber.missing':
                return 'The bankaccountnumber is missing.
                    Please check your bankaccountnumber or contact our customer service.';
            case 'en.field.bankaccountnumber.invalid':
                return 'The bankaccountnumber is missing.
                    Please check your bankaccountnumber or contact our customer service.';
            case 'en.field.currency.missing':
                return 'The currency is missing.
                    Please contact our customer service.';
            case 'en.field.currency.invalid':
                return 'The currency is invalid.
                    Please contact our customer service.';
            case 'en.field.orderline.missing':
                return 'The orderline is missing.
                    Please contact our customer service.';
            case 'en.field.orderline.invalid':
                return 'The orderline is invalid.
                    Please contact our customer service.';
            case 'en.field.totalorderamount.missing':
                return 'The total order amount is missing.
                    Please contact our customer service.';
            case 'en.field.totalorderamount.invalid':
                return 'The total order amount is invalid. This is probably due to a rounding dif ference.
                    Please contact our customer service.';
            case 'en.field.parenttransactionreference.missing':
                return 'The parent transaction reference is missing.
                    Please contact our customer service.';
            case 'en.field.parenttransactionreference.invalid':
                return 'The parent transaction reference is invalid.
                    Please contact our customer service.';
            case 'en.field.parenttransactionreference.exists':
                return 'The parent transaction reference already exists.
                    Please contact our customer service.';
            case 'en.field.vat.missing':
                return 'The vat is missing.
                    Please contact our customer service.';
            case 'en.field.vat.invalid':
                return 'The vat is invalid.
                    Please contact our customer service.';
            case 'en.field.quantity.missing':
                return 'The quantity is missing.
                    Please contact our customer service.';
            case 'en.field.quantity.invalid':
                return 'The quantity is invalid.
                    Please contact our customer service.';
            case 'en.field.unitprice.missing':
                return 'The unitprice is missing.
                    Please contact our customer service.';
            case 'en.field.unitprice.invalid':
                return 'The unitprice is invalid.
                    Please contact our customer service.';
            case 'en.field.netunitprice.missing':
                return 'The netunitprice is missing.
                    Please contact our customer service.';
            case 'en.field.netunitprice.invalid':
                return 'The netunitprice is invalid.
                    Please contact our customer service.';
               
            // Field failures in Dutch
            case 'nl.field.unknown.invalid':
                return 'Een onbekend veld is ongeldig, neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.shipto.person.initials.missing':
                return 'De initialen van het verzendadres zijn niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.initials.invalid':
                return 'De initialen van het verzendadres zijn ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.initials.missing':
                return 'De initialen van het factuuradres zijn niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.initials.invalid':
                return 'De initialen van het factuuradres zijn ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.lastname.missing':
                return 'De achternaam van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.lastname.invalid':
                return 'De achternaam van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.lastname.missing':
                return 'De achternaam van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.lastname.invalid':
                return 'De achternaam van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.city.missing':
                return 'De plaats van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.city.invalid':
                return 'De plaats van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.city.missing':
                return 'De plaats van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.city.invalid':
                return 'De plaats van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.housenumber.missing':
                return 'Het huisnummer van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.housenumber.invalid':
                return 'Het huisnummer van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.housenumber.missing':
                return 'Het huisnummer van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.housenumber.invalid':
                return 'Het huisnummer van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.postalcode.missing':
                return 'De postcode van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.postalcode.invalid':
                return 'De postcode van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.postalcode.missing':
                return 'De postcode van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.postalcode.invalid':
                return 'De postcode van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.gender.missing':
                return 'Het geslacht van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.gender.invalid':
                return 'Het geslacht van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.gender.missing':
                return 'Het geslacht van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.gender.invalid':
                return 'Het geslacht van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.housenumberaddition.missing':
                return 'De toevoeging op het huisnummer van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.housenumberaddition.invalid':
                return 'De toevoeging op het huisnummer van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.housenumberaddition.missing':
                return 'De toevoeging op het huisnummer van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.housenumberaddition.invalid':
                return 'De toevoeging op het huisnummer van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.phonenumber1.missing':
                return 'Het vaste en of mobiele telefoonnummer is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.phonenumber1.invalid':
                return 'Het vaste en of mobiele telefoonnummer is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.phonenumber2.invalid':
                return 'Het vaste en of mobiele telefoonnummer is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.emailaddress.missing':
                return 'Het e-mailadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.emailaddress.invalid':
                return 'Het e-mailadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.emailaddress.missing':
                return 'Het e-mailadres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.emailaddress.invalid':
                return 'Het e-mailadres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.dateofbirth.missing':
                return 'De geboortedatum is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.dateofbirth.invalid':
                return 'De geboortedatum is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.dateofbirth.missing':
                return 'De geboortedatum is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.dateofbirth.invalid':
                return 'De geboortedatum is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.isocountrycode.missing':
                return 'De landcode van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.isocountrycode.invalid':
                return 'De landcode van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.isocountrycode.missing':
                return 'De landcode van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.isocountrycode.invalid':
                return 'De landcode van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.prefix.missing':
                return 'De aanhef van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.person.prefix.invalid':
                return 'De aanhef van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.prefix.missing':
                return 'De aanhef van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.person.prefix.invalid':
                return 'De aanhef van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.isolanguagecode.missing':
                return 'De taal van het factuuradres is niet aanwezig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.billto.isolanguagecode.invalid':
                return 'De taal van het factuuradres is ongeldig.
                    Controleer uw factuurgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.isolanguagecode.missing':
                return 'De taal van het verzendadres is niet aanwezig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.shipto.isolanguagecode.invalid':
                return 'De taal van het verzendadres is ongeldig.
                    Controleer uw verzendgegevens of neem contact op met onze klantenservice.';
            case 'nl.field.ordernumber.missing':
                return 'Het ordernummer is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.ordernumber.invalid':
                return 'Het ordernummer is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.ordernumber.exists':
                return 'Het ordernumber bestaat al.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.bankaccountnumber.missing':
                return 'Het bankrekeningnummer is niet aanwezig.
                    Controleer uw bankrekeningnummer of neem contact op met onze klantenservice.';
            case 'nl.field.bankaccountnumber.invalid':
                return 'Het bankrekeningnummer is niet aanwezig.
                    Controleer uw bankrekeningnummer of neem contact op met onze klantenservice.';
            case 'nl.field.currency.missing':
                return 'De valuta is niet aanwezig in de aanroep.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.currency.invalid':
                return 'De valuta is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.orderline.missing':
                return 'De orderregel is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.orderline.invalid':
                return 'De orderregel is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.totalorderamount.missing':
                return 'Het totaalbedrag is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.totalorderamount.invalid':
                return 'Het totaalbedrag is ongeldig. Dit is waarschijnlijk een afrondingsverschil.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.parenttransactionreference.missing':
                return 'De referentie aan de hoofdtransactie is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.parenttransactionreference.invalid':
                return 'De referentie aan de hoofdtransactie is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.parenttransactionreference.exists':
                return 'De referentie aan de hoofdtransactie bestaat al.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.vat.missing':
                return 'De BTW is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.vat.invalid':
                return 'De BTW is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.quantity.missing':
                return 'Het aantal is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.quantity.invalid':
                return 'Het aantal is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.unitprice.missing':
                return 'De stuksprijs is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.unitprice.invalid':
                return 'De stuksprijs is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.netunitprice.missing':
                return 'De netto stuksprijs is niet aanwezig.
                    Neem alstublieft contact op met onze klantenservice.';
            case 'nl.field.netunitprice.invalid':
                return 'De netto stuksprijs is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
            default:
                return 'Een onbekend veld is ongeldig.
                    Neem alstublieft contact op met onze klantenservice.';
        }
    }
   
    /**
     * Check rejection error and give back readable error message
     *
     * @param int           $rejection_code
     *
     * @return array
     */
    public function checkRejectionError($rejection_code, $language = 'nl')
    {
        $rejection_code = $language . '.' . $rejection_code;
       
        switch ($rejection_code) {
            // Rejection errors in English
            case 'en.30':
                return array(
                    'message' => 'Customer has too many open invoices',
                    'description' => 'We are sorry to have to inform you that your request for AfterPay Open Invoice
                    on your order is not accepted by AfterPay. This is because you have reached the maximum of open
                    invoices with AfterPay.
                    We advise you to choose a dif ferent payment method to complete your order.'
                   );
            case 'en.40':
                return array(
                    'message' => 'Customer is under 18',
                    'description' => 'We are sorry to have to inform you that your request for AfterPay Open Invoice
                    on your order is not accepted by AfterPay. This is because your age is under 18. If you want to
                    use theAfterPay Open Invoice service, your age has to be 18 years or older.
                    We advise you to choose a dif ferent payment method to complete your order.'
                   );
            case 'en.42':
                return array(
                    'message' => 'Customer has no valid address',
                    'description' => 'We are sorry to have to inform you that your request for AfterPay Open Invoice
                    on your order is not accepted by AfterPay. This is because your address is not correct or not
                    complete.
                    We advise you to choose a dif ferent payment method to complete your order.'
                   );
            case 'en.36':
                return array(
                    'message' => 'Customer has no valid email address',
                    'description' => 'We are sorry to have to inform you that your request for AfterPay Open Invoice
                    on your order is not accepted by AfterPay. This is because your email address is not correct or
                    not complete.
                    We advise you to choose a dif ferent payment method to complete your order.'
                   );
           
            // Rejection errors in Dutch
            case 'nl.30':
                return array(
                    'message' => 'De consument heeft te veel openstaande orders',
                    'description' => 'Het spijt ons u te moeten mededelen dat uw aanvraag om uw bestelling achteraf
                    te betalen niet door AfterPay wordt geaccepteerd. Helaas is uw leeftijd onder de 18 jaar. Indien
                    u gebruik wilt maken van AfterPay dient uw leeftijd minimaal 18 jaar of ouder te zijn.
                    Voor vragen over uw afwijzing kunt u contact opnemen met de Klantenservice van AfterPay. Of kijk
                    op de website van AfterPay.
                    Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling
                    af te ronden.'
                   );
            case 'nl.40':
                return array(
                    'message' => 'De consument is onder 18 jaar oud',
                    'description' => 'Het spijt ons u te moeten mededelen dat uw aanvraag om uw bestelling achteraf
                    te betalen niet door AfterPay wordt geaccepteerd. This is because your age is under 18. If you
                    want to use the AfterPay Open Invoice service, your age has to be 18 years or older.
                    Voor vragen over uw afwijzing kunt u contact opnemen met de Klantenservice van AfterPay. Of
                    kijk op de website van AfterPay.
                    Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling
                    af te ronden.'
                   );
            case 'nl.42':
                return array(
                    'message' => 'De consument heeft geen geldig e-mailadres',
                    'description' => 'Het spijt ons u te moeten mededelen dat uw aanvraag om uw bestelling achteraf
                    te betalen niet door AfterPay wordt geaccepteerd. Helaas is uw adres informatie niet correct of
                    niet compleet. Indien u van AfterPay gebruik wilt maken dient het opgegeven adres een geldig
                    woon/verblijf plaats te zijn.
                    Voor vragen over uw afwijzing kunt u contact opnemen met de Klantenservice van AfterPay. Of kijk
                    op de website van AfterPay.
                    Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling
                    af te ronden.'
                   );
            case 'nl.36':
                return array(
                    'message' => 'De consument heeft geen geldig adres',
                    'description' => 'Het spijt ons u te moeten mededelen dat uw aanvraag om uw bestelling achteraf te
                    betalen niet door AfterPay wordt geaccepteerd. Helaas is het opgegeven emailadres volgens onze
                    bronnen niet volledig of bestaat het niet. Indien u van AfterPay gebruik wilt maken dient het
                    u gebruik te maken van een geldig en actief emailadres.
                    Voor vragen over uw afwijzing kunt u contact opnemen met de Klantenservice van AfterPay. Of
                    kijk op de website van AfterPay.
                    Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling
                    af te ronden.'
                   );
            default:
                return array(
                    'message' => 'Aanvraag komt niet in aanmerking voor AfterPay',
                    'description' => 'Het spijt ons u te moeten mededelen dat uw aanvraag om uw bestelling achteraf
                    te betalen niet door AfterPay wordt geaccepteerd. Dit kan om diverse (tijdelijke) redenen zijn.
                    Voor vragen over uw afwijzing kunt u contact opnemen met de Klantenservice van AfterPay. Of kijk
                    op de website van AfterPay.
                    Wij adviseren u voor een andere betaalmethode te kiezen om alsnog de betaling van uw bestelling
                    af te ronden.'
                   );
        }
    }
   
    /**
     * Check technical error and give back readable error message
     *
     * @param string        $field_failure
     *
     * @return array
     */
    public function checkTechnicalError($field_failure, $language = 'nl')
    {
        $field_failure = $language . '.' . $field_failure;
       
        switch ($field_failure) {
            // Technical errors in English
            case 'en.nl.afterpay.mercury.soap.exception.AccessDeniedException':
                return array(
                    'message' =>
                    'There was an authentication exception while connecting with the AfterPay BE webservice.',
                    'description' => 'A technical error occured, please contact the webshop.'
                   );
            case 'en.nl.afterpay.ad3.web.service.impl.exception.AuthenticationException':
                return array(
                    'message' =>
                    'There was an authentication exception while connecting with the AfterPay NL webservice.',
                    'description' => 'A technical error occured, please contact the webshop.'
                   );
               
            // Technical errors in Dutch
            case 'nl.nl.afterpay.mercury.soap.exception.AccessDeniedException':
                return array(
                    'message' =>
                    'Er was een technisch authenticatie probleem in de verbinding met de AfterPay BE webservice.',
                    'description' =>
                    'Er is een technisch probleem opgetreden, neem contact op met onze klantenservice.'
                   );
            case 'nl.nl.afterpay.ad3.web.service.impl.exception.AuthenticationException':
                return array(
                    'message' =>
                    'Er was een technisch authenticatie probleem in de verbinding met de AfterPay NL webservice.',
                    'description' =>
                    'Er is een technisch probleem opgetreden, neem contact op met onze klantenservice.'
                   );
            default:
                return array(
                    'message' => 'Er is een technisch probleem opgetreden',
                    'description' =>
                    'Er is een technisch probleem opgetreden, neem contact op met onze klantenservice.'
                   );
        }
    }
}
