<?php

return [
    'Username'          => 'DocmailUsername',
    'Password'          => 'DocmailPassword',
    "TestMode"          => true,
    'Wsdl_test'         => "https://www.cfhdocmail.com/TestAPI2/DMWS.asmx?WSDL",
    'Wsdl_live'         => "https://www.cfhdocmail.com/LiveAPI2/DMWS.asmx?WSDL",
    "Timeout"           => 240,
    
    'ProductType'       => "A4Letter",
    "IsMono"            => true,
    "IsDuplex"          => false,
    'DeliveryType'      => "Standard",
    "AddressNameFormat" => "Full Name",
    
    'DespatchASAP'      => true,
    'MinimumBalance'    => 200,
    'AlertEmail'        => "email@domain.co.uk"
];