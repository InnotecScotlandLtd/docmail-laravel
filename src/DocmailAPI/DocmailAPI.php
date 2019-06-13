<?php

namespace Softlabs\Docmail\DocmailAPI;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use nusoap_client;
use Softlabs\Docmail\Helpers;

class DocmailAPI extends DocmailAPIValidation {
    
    use Helpers;

    private $options = [];

    /**
     * Default option values
     *
     * @var array
     */
    private static $defaults = [
        "Timeout"           => 240,
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

    public function __construct($options = [])
    {
        if($options){
            $this->options = $options;
        }
    }

    /**
     * Interface to connect to "CreateMailing" API
     * @return MailingGUID
     */
    public function createMailing()
    {
        $result = $this->connectToDocmail('CreateMailing', $this->options);

        return $this->getFieldResponse($result, "MailingGUID");
    }
    
    /**
     * Interface to connect to "GetStatus" API
     * @return string
     */
    public function getStatus()
    {
        $result = $this->connectToDocmail('GetStatus', $this->options);

        return $this->getFieldResponse($result, "Status");
    }

    /**
     * Interface to connect to "GetBalance" API
     * @return string
     */
    public function getBalance() {
        return $this->connectToDocmail('GetBalance', $this->options);
    }
    
    /**
     * Interface to connect to "AddAddress" API
     * @return string
     */
    public function addAddress()
    {
        $result = $this->connectToDocmail('AddAddress', $this->options);

        return $this->getFieldResponse($result, "Success");
    }
    
    /**
     * Interface to connect to "AddTemplateFile" API
     * @return string
     */
    public function addTemplateFile()
    {
        $options = $this->options;

        if (array_key_exists('FilePath', $options)) {
            $options['FileData'] = base64_encode(File::get($options['FilePath']));
            $options['FileName'] = $this->fileInfo($options['FilePath'])['full_name'];

            unset($options['FilePath']);
        }
        
        $result = $this->connectToDocmail('AddTemplateFile', $options);
        
        return $this->getFieldResponse($result, "TemplateGUID");
    }
    
    /**
     * Interface to connect to "ProcessMailing" API
     * @return string
     */
    public function processMailing()
    {
        $result = $this->connectToDocmail('ProcessMailing', $this->options);

        return $this->getFieldResponse($result, "Success");
    }

    /**
     * Helper function to connect to docmail
     * @return mixed
     */
    private function connectToDocmail($slug, $options)
    {
        $rules = $messages = [];

        if(array_key_exists($slug, self::VALIDATION_RULES)){
            list($rules, $messages) = self::VALIDATION_RULES[$slug];
        }

        $options = $this->expandOptions($options, $rules);

        self::validateOptions($options, $rules, $messages);
        
        $url = $options['TestMode'] ? $options['Wsdl_test'] : $options['Wsdl_live'];

        $response = $this->connectViaNusoapClient($url, $slug, $options);

        $result = $response["{$slug}Result"];

        $this->throwExceptionIfErrorInResponse($result);

        return $result;
    }

    /**
     * Helper function to connect via Nusoap Client
     * @param  string $url     
     * @param  string $slug    
     * @param  array $options 
     * @param  integer $timeout 
     * @return aray         
     */
    private function connectViaNusoapClient($url, $slug, $options, $timeout = null)
    {
        $client = new nusoap_client($url, true);

        $timeout = $timeout ?? self::$defaults['Timeout'];

        $client->timeout = $timeout;
        
        set_time_limit($timeout);

        return $client->call($slug, $options);
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
        $rules = $rules + self::VALIDATION_RULES['defaults'][0];

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

    /**
     * Helper function to throw validation error
     * if there's an error in Docmail response
     * @param  string $response
     * @return Exception
     */
    private function throwExceptionIfErrorInResponse($response){

        if ($errorCode = $this->getFieldResponse($response, "Error code")) {
            $errorName    = $this->getFieldResponse($response, "Error code string");
            $errorMessage = $this->getFieldResponse($response, "Error message");

            throw new Exception("Softlabs Docmail error - Code: " . $errorCode . "; Message:" . $errorName." - ".$errorMessage);

        }
    }

    /**
     * Helper function to extract field value from
     * API response
     * @param  string $response
     * @param  string $fieldName
     * @return string
     */
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
}