<?php

namespace Softlabs\Docmail;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Softlabs\Docmail\DocmailAPI as DocmailAPI;

class Docmail {

    private $mailingGUID;
    private $templateGUID;

    /**
     * UC First for Multi-Byte String
     * @param  string $string
     * @return string
     */
    private function mbUcfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    private function sanatizeParameters($parameters)
    {
        foreach($parameters as $key => $parameter){
            unset($parameters[$key]);
            $parameters[$this->mbUcfirst($key)] = $parameter;
        }

        return $parameters;
    }

    public function sendToSingleAddress($data = [], $options = []) {
        $options = $this->sanatizeParameters( array_merge($data, $options) );
        
        $options["MailingGUID"] = DocmailAPI::createMailingNew($options);

        $isAddressAdded = DocmailAPI::addAddressNew($options);
        
        $templateGUID = DocmailAPI::addTemplateFileNew($options);

        $isProcessed = DocmailAPI::processMailingNew($options);



        $options = self::processParameterNames($options);

        try {
            DocmailAPI::validateCall(['CreateMailing'], $options);
            $mailingGUID = DocmailAPI::CreateMailing($options);
            $options["MailingGUID"] = $mailingGUID;

            DocmailAPI::validateCall(['AddAddress', 'AddTemplateFile', 'ProcessMailing'], $options);

            $result = DocmailAPI::AddAddress($options);

            $templateGUID = DocmailAPI::AddTemplateFile($options);

            $result = DocmailAPI::ProcessMailing($options);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }
    


    // Complex methods (multiple API calls)
    public static function sendToSingelAddress($data = [], $options = []) {

        $options = array_merge($data, $options);

        $options = self::processParameterNames($options);

        // try {
            DocmailAPI::validateCall(['CreateMailing'], $options);
            $mailingGUID = DocmailAPI::CreateMailing($options);
            $options["MailingGUID"] = $mailingGUID;

            DocmailAPI::validateCall(['AddAddress', 'AddTemplateFile', 'ProcessMailing'], $options);

            $result = DocmailAPI::AddAddress($options);

            $templateGUID = DocmailAPI::AddTemplateFile($options);

            $result = DocmailAPI::ProcessMailing($options);

        /*} catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }*/

        return $result;

    }

    public static function getBalance($data = [], $options = []) {

        $options = array_merge($data, $options);
        $options = self::processParameterNames($options);

        try {
            DocmailAPI::validateCall(['GetBalance'], $options);
            $result = (float)str_replace("Current balance: ", "", DocmailAPI::GetBalance($options));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }

    public static function checkBalance($data = [], $options = []) {

        $balance = self::getBalance($data, $options);

        $balanceIsOK = true;
        
        if ($balance < Config::get('docmail.MinimumBalance')) {
            $balanceIsOK    = false;
            
            $minimumBalance = self::gbp(Config::get('docmail.MinimumBalance'));
            $balance        = self::gbp($balance);

            $body = "Current Docmail balance ({$balance}) is less than minimum balance ({$minimumBalance}).";

            Mail::raw($body, function ($message){
                $message->to(Config::get('docmail.AlertEmail'));
                $message->subject('Docmail Balance Alert');
            });

            /*\View::addNamespace('package', __DIR__.'/../views');
            \Mail::send('package::alert-email', ["currentBalance" => $balance, "minimumBalance" => Config::get('docmail.MinimumBalance')], function($message)
            {
                $message->to(Config::get('docmail.AlertEmail'))->subject('Docmail balance alert');
            });*/
        }

        $ret = [
            'balanceIsOK' => $balanceIsOK,
            'balance'     => $balance,
        ];

        return $ret;
    }


    public function getMailingGUID() {
        return $this->mailingGUID;
    }

    public function getTemplateGUID() {
        return $this->templateGUID;
    }

    private static function processParameterNames($parameters) {

        // Names that should be changed to fit our standards
        $namesToConvert = [
            'PrintColour' => function($value){ return ["IsMono" => !$value]; },
            'PrintDuplex' => function($value){ return ["IsDuplex" => $value]; },
            'FirstClass'  => function($value){ return $value == true ? ["DeliveryType" => "First"] : []; },
            'PostCode'    => function($value){ return ["Address5" => $value]; },
        ];

        // Convert names to UpperCamelCase
        $processedParameters = [];
        foreach ($parameters as $key => $value) {
            $newKey = mb_strtoupper(mb_substr($key, 0, 1)) . mb_substr($key, 1);
            $processedParameters[$newKey] = $value;
        }

        // Convert names 
        foreach ($namesToConvert as $key => $func) {
            if (array_key_exists($key, $processedParameters) ) {
                $value = $processedParameters[$key];
                unset($processedParameters[$key]);
                $processedParameters = array_merge($processedParameters, $func($value));
            }
        }

        return $processedParameters;
    }

    /**
     * Converts a value to a presentable GBP format.
     *
     * @param  integer $data The data to be converted to GBP format.
     * @param  string $returnVal A value to return if the conversion is impossible.
     * @return string The formatted GBP string.
     */
    private static function gbp($data, $returnVal='-')
    {
        if (is_null($data)) {
            return $returnVal;
        }

        if ( ! is_float($data)) {
            $data = floatval($data);
        }

        return (is_numeric($data) ? '£' . number_format($data, 2) : $data);
    }
}



