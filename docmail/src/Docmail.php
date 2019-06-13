<?php

namespace Softlabs\Docmail;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

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

    public function __construct($options)
    {
        $this->options = $this->sanatizeParameters($options);
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

        $balance        = self::gbp($this->getBalance($data));
        $minimumBalance = self::gbp(Config::get('docmail.MinimumBalance'));

        $isMimumumMaintained = $balance > $minimumBalance;
        
        if($sendEmail && !$isMimumumMaintained){
            $this->sendBalanceInsufficentEmail();
        }

        return [
            'isMimumumMaintained' => $isMimumumMaintained,
            'balance'     => $balance,
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
        View::addNamespace('package', __DIR__.'/../views');

        return Mail::send('package::alert-email', compact('currentBalance', 'minimumBalance'), function($message) {
            $message->to(Config::get('docmail.AlertEmail'))->subject('Docmail balance alert');
        });
    }
}



