```php
$api = new HitPay\HitPay(API_KEY);
```


## Installing via [Composer](https://getcomposer.org/)
```bash
$ php composer.phar require Hit-pay/hitpay_php_wrapper
```

## Usage - Sandbox

```php
$api = new HitPay\HitPay(API_KEY, true);
```

### Create a new Payment Request

```php
try {
    $response = $api->paymentRequestCreate(array(
        'amount'    =>  '1.00',
        'currency'  =>  'sgd'
        ));
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the Payment Request that was just created.


### Get the details of a Payment Request

```php
try {
    $response = $api->paymentRequestStatus('[PAYMENT REQUEST KEY]');
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the Payment Request.

Here `['PAYMENT REQUEST KEY']` is the value of `'key'` key returned by the `paymentRequestStatus()` query.


### Get a list of all Payment Request

```php
try {
    $response = $api->paymentRequestsList();
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you an array containing Payment Requests created so far.

## Available Payment Request Functions

You have these functions to interact with the Payment Request API:

  * `paymentRequestCreate(array $paymentRequest)` Create a new Payment Request.
  * `paymentRequestStatus($key)` Get details of Payment Request specified by its unique key.
  * `paymentRequestsList()` Get a list of all Payment Requests.

## Payment Request Creation Parameters

### Required
  * `amount`: Payment Request ID for which Payment Request is being requested.
  * `currency`: A three letter currency-code.

Further documentation is available at https://hit-pay.local/api/documentation