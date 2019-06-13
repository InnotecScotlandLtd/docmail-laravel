# docmail-laravel
Docmail Laravel package can be easily installed using composer.

Update `require` and `minimum stability` in your `composer.json`  as follows:
```
{
    "require": {
        "laravel/framework": "5.8.*",
        "econea/nusoap": "0.9.5.1",
        "softlabs/docmail": "dev-master"
    },
    "minimum-stability": "dev"
}
```
This package has dependencies over the following sub-packages:
 - illuminate/support
 - econea/nusoap


Add `repository` in `composer.json`:
```
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/InnotecScotlandLtd/docmail-laravel"
        }
    ]
```

Run `composer update`

------
This package is compatible with Laravel's auto-discovery feature. You can publish the configuration files by using command:


`php artisan vendor:publish --provider=Softlabs\Docmail\DocmailServiceProvider`

Package follows PSR-4 autoloading and the code is bootstrapped under `Softlabs\Docmail` namespace.

This package 

## Examples:


This package provides an easy interface to interact with the Docmail API:

### Get Balance

```
$docMailService = new Softlabs\Docmail\Docmail
$docMailService->getBalance();
```

Response(`float`): `99.00`


### Check If Mimimum Balance is maintained
```
$docMailService = new Softlabs\Docmail\Docmail
$docMailService->checkBalance();
```
In case a user fails to maintain the minimum balance, a notification email is sent to the user. 

Response:
```
[
     "isMinimumMaintained" => false
     "balance" => 99.00
]
```

*Note*: Email can be prevented by`$docMailService->checkBalance(false);`

### Create  A Mailing

```
    $options = [
        'ProductType'       => 'A4Letter',
        'MailingName'       => '0012',
        "IsMono"            => true,
        "IsDuplex"          => false,
        'DeliveryType'      => "Standard",
        "AddressNameFormat" => "Full Name"
    ];

    $docMailService = new Softlabs\Docmail\Docmail;
    $docMailService->createMailing($options);
```

The response contains `mailingGUID` which is updated as a private property in the class. 

A helper function `$docMailService->getMailingGUID()` can be used to get this value.

### Add Address

```
$options = [
    'mailingGUID' => '06b64d3f-b7a0-4d9d-a237-a9949cb46f3e',
    'companyName'  => 'ABC',
    'firstName'    => 'John',
    'surname'      => 'Doe',
    'address1'     => 'Address 1',
    'address2'     => 'Address 2',
    'address3'     => 'Address 3',
    'address4'     => 'Address 4',
    'postCode'     => '12345',
];

$docMailService = new Softlabs\Docmail\Docmail;
$docMailService->addAddress($options);
```


### Add A Tempate File
```
$options = [
    'mailingGUID'  => '06b64d3f-b7a0-4d9d-a237-a9949cb46f3e',
    'FilePath'     => public_path('sample.pdf'),
    'TemplateName' => 'Invoice',
    'DocumentType' => 'A4Letter'
];

$docMailService = new Softlabs\Docmail\Docmail;
$docMailService->addTemplateFile($options)

```
The response contains `templateGUID` which is updated as a private property in the class. 

A helper function `$docMailService->getTemplateGUID()` can be used to get this value.


### Process Mailing
```
$options =  [
    'mailingGUID'        => '06b64d3f-b7a0-4d9d-a237-a9949cb46f3e',
    'ProductType'       => 'A4Letter',
    'MailingName'       => '0012',
    "IsMono"              => true,
    "IsDuplex"            => false,
    'DeliveryType'      => "Standard",
    "AddressNameFormat" => "Full Name"
];

$docMailService = new Softlabs\Docmail\Docmail;
$docMailService->processMailing($options)
```

---

## Method Chaining

Package also support method chaining:
```
$data = [
    'companyName'  => 'ABC',
    'firstName'    => 'John',
    'surname'      => 'Doe',
    'address1'     => 'Address 1',
    'address2'     => 'Address 2',
    'address3'     => 'Address 3',
    'address4'     => 'Address 4',
    'postCode'     => '12345',
    'mailingName'  => '0012',
    'filePath'     => public_path('sample.pdf'),
    'templateName' => 'Invoice'
];

$docMailService->createMailing($data)
                            ->addAddress()
                            ->addTemplateFile()
                            ->processMailing();
```
`MailingGUID`  generated by `createMailing` is traversed and used by subsequent methods like add `addTemplateFile` and `processMailing`