MPU Payment Integration Package
============

<!-- [![Latest Stable Version](https://packagist.org/packages/kennebula/mpupaymentintegration)] -->

Requirements
------------

* PHP >= 8.0;
* composer;

Features
--------

* PSR-4 autoloading compliant structure.
* Easy to use with Laravel framework.
* Useful tools for better code included.

Installation
============

    composer require kennebula/mpupaymentintegration

Set Up Tools
============

Running Command:
--------------------------

    php artisan vendor:publish --provider="KenNebula\MPUPaymentIntegration\PackageServiceProvider" --tag="config"

Config Output
----------

    return [
        #to fill payment uat url 
        'uat' => null,
        #to fill payment production url
        'production' => null,
        #to fill merchant ID
        'merchantID' => null,
        #to fill currency Code
        'currencyCode' => null,
        #to fill product description
        'product Desc' => null,
        #to fill secret key
        'secretKey' => null,
        #to fill frontend URL
        'frontendURL' => null,
        #to fill backend URL
        'backendURL' => null
    ];

* This command will create mpu.php file inside config folder like this, 

* Important - You need fill the mpu info in this config file for package usage.

Package Usage
------------

Send Payment (to get redirect url) :
----------------

    use KenNebula\MPUPaymentIntegration\mpu;

    MPU::sendPayment(@multidimensionalArray $items,@String $customer_name, @Int $total_amount, @String $merchant_order_no);
* Note 

* items array must be include name, amount, quantity.
* customerName must be string.
* totalAmount must be integer.
* merchantOrderId must be string.

Load Output 
---------

* This will generate a mpu prebuild form url.    

Extract Callback Data:
----------------

    use KenNebula\MPUPaymentIntegration\mpu;

    mpu::callback(@String $paymentResult,@String $checkSum);

* Note 

* paymentResult must be string.
* checkSum must be string.

Callback Output 
------

* This will return decrypted data array include payment information.  

License
=======

KenNebula Reserved Since 2024.