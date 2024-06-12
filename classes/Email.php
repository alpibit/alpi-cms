<?php
class Email
{
    private $to;
    private $subject;
    private $message;
    private $headers;

    public function __construct($to, $subject, $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = [];
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function send()
    {
        $headerString = '';
        foreach ($this->headers as $name => $value) {
            $headerString .= "$name: $value\r\n";
        }

        if (mail($this->to, $this->subject, $this->message, $headerString)) {
            error_log("Email sent successfully to: {$this->to}");
            return true;
        } else {
            error_log("Failed to send email to: {$this->to}");
            return false;
        }
    }
}
