<?php

namespace Softlabs\Docmail\DocmailAPI;

use Exception;
use Illuminate\Support\Facades\Validator;

class DocmailAPIValidation {

    const VALIDATION_RULES = [
        'defaults' => [
            [
                'Username'            => 'required|max:100',
                'Password'            => 'required|max:100',
                'Wsdl_test'           => 'required|max:100',
                'Wsdl_live'           => 'required|max:100',
                'timeout'             => 'required|max:100',
                "TestMode"            => "required",
                "CustomerApplication" => "",
                "DespatchASAP"        => "",
                "ProductType"         => "",
                "IsMono"              => "",
                "IsDuplex"            => "",
                "DeliveryType"        => "",
                "CanBeginOnBack"      => ""
            ],

            [
                'Username'            => 'Username is required',
                'Password'            => 'Password is required',
                'Wsdl_test'           => 'Test WSDL is required',
                'Wsdl_live'           => 'Live WSDL is required',
                'timeout'             => 'Timeout is required',
                "TestMode"            => "TestMode is Required",
                "CustomerApplication" => "",
                "DespatchASAP"        => "",
                "ProductType"         => "",
                "IsMono"              => "",
                "IsDuplex"            => "",
                "DeliveryType"        => "",
                "CanBeginOnBack"      => ""
            ]
        ],
        'CreateMailing' => [
            [
                'MailingName'       => 'required',
                'IsMono'            => 'required',
                'IsDuplex'          => 'required',
                'DeliveryType'      => 'required',
                'AddressNameFormat' => 'required',
                'ProductType'       => 'required',
            ],
            [
                'MailingName'       => 'MailingName is required',
                'IsMono'            => 'IsMono is required',
                'IsDuplex'          => 'IsDuplex is required',
                'DeliveryType'      => 'DeliveryType is required',
                'AddressNameFormat' => 'AddressNameFormat is required',
                'ProductType'       => 'ProductType is required',
            ]
        ],

        'GetStatus' => [
            ['MailingGUID'     => 'required'],
            ['MailingGUID' => 'MailingGUID is required'],
        ],

        'AddAddress' => [
            [
                'MailingGUID' => 'required',
                'Address1'    => 'required',
            ],
            [
                'MailingGUID' => 'MailingGUID is required',
                'Address1'    => 'Address1 is required',
            ],
        ],

        'AddTemplateFile' => [
            [
                'MailingGUID'  => 'required',
                "FileData"     => 'required',
                "DocumentType" => 'required',
                "FileName"     => 'required',
                "TemplateName" => 'required',
            ],
            [
                'MailingGUID' => 'MailingGUID is required',
                "FileData"     => 'FileData is required',
                "DocumentType" => 'DocumentType is required',
                "FileName"     => 'FileName is required',
                "TemplateName" => 'TemplateName is required',
            ]
        ],

        'ProcessMailing' => [
            [
                'MailingGUID'       => 'required',
                'MailingName'       => 'required',
                'IsMono'            => 'required',
                'IsDuplex'          => 'required',
                'DeliveryType'      => 'required',
                'AddressNameFormat' => 'required',
                'ProductType'       => 'required',
            ],
            [
                'MailingGUID'       => 'MailingGUID is required',
                'MailingName'       => 'MailingName is required',
                'IsMono'            => 'IsMono is required',
                'IsDuplex'          => 'IsDuplex is required',
                'DeliveryType'      => 'DeliveryType is required',
                'AddressNameFormat' => 'AddressNameFormat is required',
                'ProductType'       => 'ProductType is required',
            ],
        ],
    ];

    /**
     * Validates options.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    public static function validateOptions($options, $rules, $messages) {

        // Add default validation messages to $messages parameter array
        $defaults = self::VALIDATION_RULES['defaults'];
        
        $messages = $messages + end($defaults);

        // Validate options against rules
        $validator = Validator::make($options, $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->messages();
            throw new Exception("Validation error: " . print_r($messages->all(), true), 1);
        }
    }
}