<?php

namespace Softlabs\Docmail;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Softlabs\Docmail\DocmailAPI\DocmailAPIMutators;

class Docmail extends DocmailAPIMutators {

    use Helpers;

    /**
     * MailGUID of order
     * @var string
     */
    private $mailingGUID;
    
    /**
     * TemplateGUID of the order
     * @var string
     */
    private $templateGUID;

    /**
     * Default configuration options arra
     * @var array
     */
    public $options = [];

    public function __construct($options = [])
    {
        if($options){
            $this->options = $this->sanatizeParameters($options);
        }
    }

    /**
     * Get status of an order
     * @param  array  $data
     * @return string
     */
    public function getStatus($data = [])
    {
        $this->refreshOptions($data);

        return $this->docmailApiSingleton()->getStatus();
    }

    /**
     * Get balance of user
     * @param  array  $data]
     * @return float
     */
    public function getBalance($data = []) {
        $this->refreshOptions($data);
        
        $balanceExploded = explode('Current balance: ', $this->docmailApiSingleton()->getBalance());

        return (float) end($balanceExploded);
    }

    /**
     * Check balance of user, and send email if mimimum is not maintained
     * @param  boolean  sendEmail
     * @param  array  $data
     * @return array
     */
    public function checkBalance($sendEmail = true, $data = []) {
        
        $this->refreshOptions($data);
        
        $currentBalance = $this->getBalance($data);
        $minimumBalance = Config::get('docmail.MinimumBalance');

        $isMinimumMaintained = $currentBalance > $minimumBalance;
        
        if($sendEmail && !$isMinimumMaintained){
            $this->sendBalanceInsufficentEmail($currentBalance, $minimumBalance);
        }

        return [
            'isMinimumMaintained' => $isMinimumMaintained,
            'balance'             => $currentBalance,
        ];
    }

    /**
     * Get Mailing GUID of the order
     * @return string
     */
    public function getMailingGUID() {
        return $this->mailingGUID;
    }

    /**
     * Get Template GUID of the order
     * @return string
     */
    public function getTemplateGUID() {
        return $this->templateGUID;
    }

    /**
     * Set MailGUID of order
     * @param string $mailingGUID
     */
    public function setMailingGUID($mailingGUID)
    {
        $this->options['MailingGUID'] = $mailingGUID;
        
        $this->mailingGUID = $mailingGUID;

        $this->refreshDocmailApiSingleton();
    }

    /**
     * Set Template GUID in the class
     * @param string $templateGUID
     */
    public function setTemplateGUID($templateGUID)
    {
        $this->options['TemplateGUID'] = $templateGUID;
        
        $this->templateGUID = $templateGUID;

        $this->refreshDocmailApiSingleton();
    }

    /**
     * Send email to user containing details of 
     * his current balance and require balance
     * @param  float $currentBalance
     * @param  float $minimumBalance
     * @return return boolean
     */
    private function sendBalanceInsufficentEmail($currentBalance, $minimumBalance)
    {
        // Possibility of using changing currency in view
        /*$currentBalance = $this->gbp($currentBalance);
        $minimumBalance = $this->gbp($minimumBalance);*/

        View::addNamespace('docmail', __DIR__.'/views');

        return Mail::send('docmail::alert-email', compact('currentBalance', 'minimumBalance'), function($message) {
            $message->to(Config::get('docmail.AlertEmail'))->subject('Docmail balance alert');
        });
    }
}



