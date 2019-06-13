<?php

namespace Softlabs\Docmail;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use nusoap_client;

class DocmailAPI {

    private static $validateOnly = false;

    private $options = [];

    /**
     * Default option values
     *
     * @var array
     */
    private static $defaults = [
        "timeout"           => 240,
        "DocumentType"      => "A4Letter",
        "ProductType"       => "A4Letter",
        "IsMono"            => true,
        "IsDuplex"          => false,
        "DeliveryType"      => "Standard",
        "AddressNameFormat" => "Full Name",
        "TestMode"          => true,
        "DespatchASAP"      => true,
        "CanBeginOnBack"    => false
    ];

    /**
     * Validation rules for option items that are always required
     *
     * @var array
     */
    private static $validationRules = [
        'Username'            => 'required|max:100',
        'Password'            => 'required|max:100',
        'wsdl_test'           => 'required|max:100',
        'wsdl_live'           => 'required|max:100',
        'timeout'             => 'required|max:100',
        "TestMode"            => "required",
        "CustomerApplication" => "",
        "DespatchASAP"        => "",
        "ProductType"         => "",
        "IsMono"              => "",
        "IsDuplex"            => "",
        "DeliveryType"        => "",
        "CanBeginOnBack"      => ""
    ];

    /**
     * Validation messages for option items that are always required
     *
     * @var array
     */
    private static $validationMessages = [
        'Username'            => 'Username is required',
        'Password'            => 'Password is required',
        'wsdl_test'           => 'Test WSDL is required',
        'wsdl_live'           => 'Live WSDL is required',
        'timeout'             => 'Timeout is required',
        "TestMode"            => "TestMode is Required",
        "CustomerApplication" => "",
        "DespatchASAP"        => "",
        "ProductType"         => "",
        "IsMono"              => "",
        "IsDuplex"            => "",
        "DeliveryType"        => "",
        "CanBeginOnBack"      => ""
    ];

    public function __construct($options = [])
    {
        if($options){
            $this->options = $options;
        }
    }

    public function createMailing()
    {
        $messages = [
            'MailingName'       => 'MailingName is required',
            'IsMono'            => 'IsMono is required',
            'IsDuplex'          => 'IsDuplex is required',
            'DeliveryType'      => 'DeliveryType is required',
            'AddressNameFormat' => 'AddressNameFormat is required',
            'ProductType'       => 'ProductType is required',
        ];

        $rules = [
            'MailingName'       => 'required',
            'IsMono'            => 'required',
            'IsDuplex'          => 'required',
            'DeliveryType'      => 'required',
            'AddressNameFormat' => 'required',
            'ProductType'       => 'required',
        ];

        $result = $this->connectToDocmail('CreateMailing', $this->options, $rules, $messages);

        return $this->getFieldResponse($result, "MailingGUID");
    }

    public function getStatus()
    {
        $messages = [
            'MailingGUID' => 'MailingGUID is required',
        ];

        $rules = [
            'MailingGUID'     => 'required',
        ];

        $result = $this->connectToDocmail('GetStatus', $this->options);

        return $this->getFieldResponse($result, "Status");
    }

    /**
     * GetBalance API call
     *
     * @param  array   $options 
     * @return string  Status
     */
    public function getBalance() {
        return $this->connectToDocmail('GetBalance', $this->options);
    }

    public function addAddress()
    {
        $rules = [
            'MailingGUID' => 'required',
            'Address1'    => 'required',
        ];

        $messages = [
            'MailingGUID' => 'MailingGUID is required',
            'Address1'    => 'Address1 is required',
        ];

        $result = $this->connectToDocmail('AddAddress', $this->options, $rules, $messages);

        return $this->getFieldResponse($result, "Success");
    }

    private function connectToDocmail($slug, $options, $rules = [], $messages = [])
    {
        $options = $this->expandOptions($options, $rules);

        self::validateOptions($options, $rules, $messages);
        
        $url = $options['TestMode'] ? $options['wsdl_test'] : $options['wsdl_live'];

        $response = self::connectToApi($url, $slug, $options);

        $result = $response["{$slug}Result"];

        $this->throwExceptionIfErrorInResponse($result);

        return $result;
    }

    public function addTemplateFile()
    {
        $messages = [
            'MailingGUID' => 'MailingGUID is required',
            "FileData"     => 'FileData is required',
            "DocumentType" => 'DocumentType is required',
            "FileName"     => 'FileName is required',
            "TemplateName" => 'TemplateName is required',
        ];

        $rules = [
            'MailingGUID'  => 'required',
            "FileData"     => 'required',
            "DocumentType" => 'required',
            "FileName"     => 'required',
            "TemplateName" => 'required',
        ];

        $options = $this->options;

        if (array_key_exists('FilePath', $options)) {
            $options['FileData'] = base64_encode(File::get($options['FilePath']));
            $options['FileName'] = self::fileInfo($options['FilePath'])['full_name'];

            unset($options['FilePath']);
        }
        
        $result = $this->connectToDocmail('AddTemplateFile', $options, $rules, $messages);
        
        return $this->getFieldResponse($result, "TemplateGUID");
    }

    /**
     * [fileInfo description]
     * @param  [type] $filePath [description]
     * @return [type]           [description]
     */
    private static function fileInfo($filePath)
    {
        $pathInfo = pathinfo($filePath);

        $file = [];

        $file['name']      = $pathInfo['filename'];
        $file['extension'] = $pathInfo['extension'];
        $file['full_name'] = $pathInfo['filename'] . "." . $file['extension'];

        return $file;
    }

    public function processMailing()
    {
        $messages = [
            'MailingName'       => 'MailingName is required',
            'IsMono'            => 'IsMono is required',
            'IsDuplex'          => 'IsDuplex is required',
            'DeliveryType'      => 'DeliveryType is required',
            'AddressNameFormat' => 'AddressNameFormat is required',
            'ProductType'       => 'ProductType is required',
        ];

        $rules = [
            'MailingName'       => 'required',
            'IsMono'            => 'required',
            'IsDuplex'          => 'required',
            'DeliveryType'      => 'required',
            'AddressNameFormat' => 'required',
            'ProductType'       => 'required',
        ];

        $result = $this->connectToDocmail('ProcessMailing', $this->options, $rules, $messages);

        return $this->getFieldResponse($result, "Success");
    }

    public function getTemplateGUID()
    {
        return $this->options['TemplateGUID'] ?? null;
    }

    private static function connectToApi($url, $slug, $options, $timeout = null)
    {
        $client = new nusoap_client($url, true);

        $timeout = $timeout ?? self::$defaults['timeout'];

        $client->timeout = $timeout;
        
        set_time_limit($timeout);

        return $client->call($slug, $options);
    }

    // Low level methods

    /**
     * Validates options.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    private static function validateOptions($options, $rules, $messages) {

        // Add default validation messages to $messages parameter array
        $messages = $messages + self::$validationMessages;

        // Validate options against rules
        $validator = Validator::make($options, $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->messages();
            throw new Exception("Validation error: " . print_r($messages->all(), true), 1);
        }
    }


    /**
     * Get options and try to add default values if required item is missing.
     *
     * @param  array  $options
     * @param  array  $rules
     * @return array
     */
    private function expandOptions($options, $rules) {

        // Add default validation rules to $rules parameter array
        $rules = $rules + self::$validationRules;

        foreach ($rules as $key => $ruleString) {

            $$key = (array_key_exists($key, $options) ? $options[$key] : Config::get('docmail.' . $key));

            if ($$key === null and array_key_exists($key, self::$defaults)) {
                $$key = self::$defaults[$key];
            }

            if ($$key === null) {
                unset($options[$key]);
            } else {
                $options[$key] = $$key;
            }

        }

        return $options;
    }

    private function throwExceptionIfErrorInResponse($response){

        if ($errorCode = $this->getFieldResponse($response, "Error code")) {
            $errorName    = $this->getFieldResponse($response, "Error code string");
            $errorMessage = $this->getFieldResponse($response, "Error message");

            throw new Exception("Softlabs Docmail error - Code: " . $errorCode . "; Message:" . $errorName." - ".$errorMessage);

        }
    }

    // These functions are copied from the example code


    private function getFieldResponse($response, $fieldName)
    {
        $lines = explode("\n", $response);
        
        for ( $lineCounter=0; $lineCounter < count($lines); $lineCounter+=1){
            
            $fields = explode(":", $lines[$lineCounter]);

            if ($fields[0] == $fieldName)   {
                return ltrim($fields[1], " ");
            }
        }
    }
    
    private static function GetFld($FldList,$FldName){
        // calls return a multi-line string structured as :
        // [KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE][carriage return][line feed][KEY]: [VALUE]
        //explode lines
        //print "Looking for Field '".$FldName."'<br>";
        $lines = explode("\n",$FldList);
        for ( $lineCounter=0;$lineCounter < count($lines); $lineCounter+=1){
            //explode field/value
            $fields = explode(":",$lines[$lineCounter]);
            //find matching field name
            if ($fields[0]==$FldName)   {
                //print "'".$FldName."' Value: ".ltrim($fields[1], " ")."<br>";
                return ltrim($fields[1], " "); //return value
            }
        }
        //print "'".$FldName."' NOT found<br>";
    }
}