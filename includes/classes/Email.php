<?php

class Email
{
    private $recipientAddress;
    private $subject;
    private $body;

    public function __construct($recipientAddress, $subject, $body)
    {    
        $this->recipientAddress = $recipientAddress;
        $this->subject = $subject;
        $this->body = $body;
    }

    public function getRecipientAddress()
    {
        return $this->recipientAddress;
    }

    public function setRecipientAddress($recipientAddress)
    {
        $this->recipientAddress = $recipientAddress;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

}