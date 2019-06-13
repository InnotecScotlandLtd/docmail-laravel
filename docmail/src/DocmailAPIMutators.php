<?php

namespace Softlabs\Docmail;

abstract class DocmailAPIMutators
{
    /**
     * Singleton Instance of DocMail API
     * @var Softlabs\Docmail\DocmailAPI
     */
    protected $docmailApiSingleton;

    use Helpers;

    /**
     * Chaining function for creating mailing address
     * @param  array  $data
     * @return $this
     */
    public function createMailing($data = [])
    {
        $this->refreshOptions($data);
        
        $this->setMailingGUID($this->docmailApiSingleton()->createMailing());

        return $this;
    }

    /**
     * Chaining function for creating new address
     * @param array $data
     * @return $this
     */
    public function addAddress($data = [])
    {
        $this->refreshOptions($data);

        $this->docmailApiSingleton()->addAddress();

        return $this;
    }

    /**
     * Chaining function for creating new template
     * @param array $data
     * @return $this
     */
    public function addTemplateFile($data = [])
    {
        $this->refreshOptions($data);

        $this->setTemplateGUID( $this->docmailApiSingleton()->addTemplateFile() );

        return $this;
    }

    /**
     * Chaining function for processing email
     * @param  array  $data
     * @return $this
     */
    public function processMailing($data = [])
    {
        $this->refreshOptions($data);

        $this->docmailApiSingleton()->processMailing();

        return $this;
    }

    /**
     * Helper function to sync new options with
     * existing options
     * @param  array  $data
     * @return null
     */
    protected function refreshOptions($data = [])
    {
        $this->options = array_merge(
                            $this->sanatizeParameters($data),
                            $this->options
                        ) ;
    }

    /**
     * Helper function to refresh options in
     * docmail api instance
     * @return null
     */
    protected function refreshDocmailApiSingleton()
    {
        $this->docmailApiSingleton = new DocmailAPI($this->options);
    }
    
    /**
     * Singleton Instance of Docmail API
     * @return \Softlabs\Docmail\DocmailAPI
     */
    protected function docmailApiSingleton()
    {
        if($docmailApiSingleton = $this->docmailApiSingleton){
            return $docmailApiSingleton;
        }

        $this->docmailApiSingleton = new DocmailAPI($this->options);

        return $this->docmailApiSingleton;
    }

    /**
     * Make all keys uppercase
     * and then replace keys to match with docmail
     * @param  array $parameters
     * @return array
     */
    protected function sanatizeParameters($parameters)
    {
        foreach($parameters as $key => $parameter){
            unset($parameters[$key]);
            $parameters[$this->mbUcfirst($key)] = $parameter;
        }

        return $this->convertParameterValues($parameters);
    }

    private function convertParameterValues($parameters)
    {
        $namesToConvert = [
            'PrintColour' => function($value){ return ["IsMono" => !$value]; },
            'PrintDuplex' => function($value){ return ["IsDuplex" => $value]; },
            'FirstClass'  => function($value){ return $value == true ? ["DeliveryType" => "First"] : []; },
            'PostCode'    => function($value){ return ["Address5" => $value]; },
        ];

        foreach ($namesToConvert as $key => $closure) {
            if (array_key_exists($key, $parameters) ) {
                $value = $parameters[$key];
                unset($parameters[$key]);
                $parameters = array_merge($parameters, $closure($value));
            }
        }

        return $parameters;
    }
}