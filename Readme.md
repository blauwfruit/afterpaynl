<p align="center">
  <img src="https://blauwfruit.nl/logos/logo.svg" width="300"/>
</p>


PrestaShop Module for AfterPay: Achteraf betalen
===============

## How to install the module?

* Download the latest version of the module (the '.zip' file) via the [Releases page](https://github.com/blauwfruit/afterpaynl/releases) which is compatible with PrestaShop 1.6 to 1.7;
* Go to the backoffice of your PrestaShop webshop;
* In your backoffice, go to 'Modules' and then 'Module Manager' and choose 'Upload a module';
* Click on 'select file' and upload the .zip file.

## Configuration

* Modules &rarr; Module Manager &rarr; Achteraf betalen met AfterPay
* After the module has been installed, click on 'Configure';
* Enter your 'Merchant ID', 'Portfolio ID' and 'Password' (provided by AfterPay). You should do this for every applicable account: B2C, B2B and B2C Belgium;
* If you have enabled the PrestaStore Multistore functionality, fill out these details per store;
* Click on 'Save'.

## Address format

To be absolutely sure that people won’t be rejected because of wrong address details, we strongly advise to change the address format:

* Localisation &rarr; Country &rarr; Netherlands; 
* Then, under 'Addres format', you can rearrange the order to: 
```
firstname lastname
company
vat_number
address1 address2
postcode city
Country:name
phone_mobile
phone
```
* Click on 'Save'.

`address2` is used as the house number for AfterPay. It is important to make it mandatory. Also, in PrestaShop 1.7, make sure you make the phone number obligatory. Otherwise the customer will be rejected: 

* Customers → Addresses → click on the button 'Set required fields for this section';
* Select `address2`; 
* Unselect `phone` and/or `phone_mobile`;
* Click on 'Save'.