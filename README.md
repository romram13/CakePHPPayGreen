# Paygreen Plugin for CakePHP 2.x

### Why this plugin ?

Paygreen has a really simple API. To make a CakePHP integration even easier, I made this plugin which include a Paygreen Component. You just have to load the plugin and the component.

Official Paygreen website : [http://www.paygreen.info]

### What is easier with the Paygreen Component ?

You just have to fill the necessary datas. CakePHP will provide the right Controller.

### Requirements

* CakePHP 2.x
* A Paygreen account

### Installation

_[Manual]_

* Download
* Unzip that download.
* Copy the resulting folder to `app/Plugin`
* Rename the folder you just copied to `Paygreen`



### Usage

First, add the component in your Controller

```php
public $components = array('Paygreen', 'Paygreen');
```


## PaymentRedirect
Initialize the Paygreen Component and just launch the paiement.

```php
	$configPaygreen = [
		'paygreen_privatekey'   => 'paygreen_privatekey',
		'paygreen_publickey'    => 'paygreen_publickey',
		'amount'                => 'amount',
		'id'                    => 'id',
		'lastname'              => 'lastname' ,
		'firstname'             => 'firstname',
		'email'                 => 'email',
		'actionReturn'          => 'return_paygreen', //Action utilisée pour le retour de paiement
		'actionNotification'    => 'notification_paygreen', //Action utilisée pour la notification de paiement
		'actionCancel'          => 'cancel_paygreen' //Action utilisée pour l'annulation du paiement
	];

	$this->Paygreen->configuration($configPaygreen);
	$this->Paygreen->PaymentRedirect();
```

Your client will be automaticaly redirected to Paygreen.

## ReturnPayment

In your return action.

```php
		$configPaygreen = [
			'paygreen_privatekey'   => $config['paygreen_privatekey'],
			'paygreen_publickey'    => $config['paygreen_publickey']
		];
		$this->Paygreen->configuration($configPaygreen);
		$paygreenData = $this->Paygreen->ReturnPayment($this->request->data);
```
In this example "$paygreenData" will be an array with the response of Paygreen.

## CancelPayment

In your return action.

```php
		$configPaygreen = [
			'paygreen_privatekey'   => $config['paygreen_privatekey'],
			'paygreen_publickey'    => $config['paygreen_publickey']
		];
		$this->Paygreen->configuration($configPaygreen);
		$paygreenData = $this->Paygreen->CancelPayment($this->request->data);
```
As in ReturnPayment, "$paygreenData" will be an array with the response of Paygreen.


### License

Copyright 2016, Romain Ramier

Licensed under The MIT License 