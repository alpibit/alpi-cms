<?php

class Email
{
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $from = '';
    private $replyTo = '';
    private $subject = '';
    private $message = '';
    private $altMessage = '';
    private $headers = [];
    private $attachments = [];

    private $smtpHost = '';
    private $smtpPort = 25;
    private $smtpUser = '';
    private $smtpPass = '';
    private $smtpSecure = ''; // 'ssl' or 'tls'

    public function __construct()
    {
        $this->headers['MIME-Version'] = '1.0';
        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';
    }

    public function setTo($address, $name = '')
    {
        $this->to[] = $this->formatAddress($address, $name);
    }

    public function setCC($address, $name = '')
    {
        $this->cc[] = $this->formatAddress($address, $name);
    }

    public function setBCC($address, $name = '')
    {
        $this->bcc[] = $this->formatAddress($address, $name);
    }

    public function setFrom($address, $name = '')
    {
        $this->from = $this->formatAddress($address, $name);
    }

    public function setReplyTo($address, $name = '')
    {
        $this->replyTo = $this->formatAddress($address, $name);
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setAltMessage($altMessage)
    {
        $this->altMessage = $altMessage;
    }

    public function addAttachment($path, $name = '')
    {
        $this->attachments[] = [
            'path' => $path,
            'name' => $name ?: basename($path)
        ];
    }

    public function setSmtpSettings($host, $port, $user, $pass, $secure = '')
    {
        $this->smtpHost = $host;
        $this->smtpPort = $port;
        $this->smtpUser = $user;
        $this->smtpPass = $pass;
        $this->smtpSecure = $secure;
    }

    public function send()
    {
        $this->buildHeaders();
        $body = $this->buildBody();

        if ($this->smtpHost) {
            return $this->sendSmtp($body);
        } else {
            return $this->sendMail($body);
        }
    }

    private function sendMail($body)
    {
        $to = implode(', ', $this->to);
        $subject = $this->subject;
        $headers = $this->buildHeaderString();

        if (mail($to, $subject, $body, $headers)) {
            error_log("Email sent successfully to: {$to}");
            return true;
        } else {
            error_log("Failed to send email to: {$to}");
            return false;
        }
    }

    private function sendSmtp($body)
    {
        $errno = $errstr = '';
        $socket = fsockopen(
            ($this->smtpSecure === 'ssl' ? 'ssl://' : '') . $this->smtpHost,
            $this->smtpPort,
            $errno,
            $errstr,
            15
        );

        if (!$socket) {
            error_log("SMTP connection failed: {$errno} - {$errstr}");
            return false;
        }

        $this->serverParse($socket, 220);
        $this->sendCommand($socket, "EHLO " . $_SERVER['SERVER_NAME']);

        if ($this->smtpSecure === 'tls') {
            $this->sendCommand($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand($socket, "EHLO " . $_SERVER['SERVER_NAME']);
        }

        if ($this->smtpUser && $this->smtpPass) {
            $this->sendCommand($socket, "AUTH LOGIN");
            $this->sendCommand($socket, base64_encode($this->smtpUser));
            $this->sendCommand($socket, base64_encode($this->smtpPass));
        }

        $this->sendCommand($socket, "MAIL FROM: <{$this->extractEmail($this->from)}>");
        foreach (array_merge($this->to, $this->cc, $this->bcc) as $recipient) {
            $this->sendCommand($socket, "RCPT TO: <{$this->extractEmail($recipient)}>");
        }

        $this->sendCommand($socket, "DATA");
        fwrite($socket, $this->buildHeaderString() . "\r\n" . $body . "\r\n.\r\n");
        $this->serverParse($socket, 250);

        $this->sendCommand($socket, "QUIT");
        fclose($socket);

        error_log("Email sent successfully via SMTP");
        return true;
    }

    private function buildHeaders()
    {
        if ($this->from) {
            $this->headers['From'] = $this->from;
        }
        if ($this->replyTo) {
            $this->headers['Reply-To'] = $this->replyTo;
        }
        if (!empty($this->cc)) {
            $this->headers['Cc'] = implode(', ', $this->cc);
        }
        if (!empty($this->bcc)) {
            $this->headers['Bcc'] = implode(', ', $this->bcc);
        }
    }

    private function buildHeaderString()
    {
        $headerString = '';
        foreach ($this->headers as $name => $value) {
            $headerString .= "{$name}: {$value}\r\n";
        }
        return $headerString;
    }

    private function buildBody()
    {
        $boundary = md5(time());
        $this->headers['Content-Type'] = "multipart/alternative; boundary={$boundary}";

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($this->altMessage ?: strip_tags($this->message)));

        $body .= "\r\n--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($this->message));

        foreach ($this->attachments as $attachment) {
            $body .= "\r\n--{$boundary}\r\n";
            $body .= "Content-Type: application/octet-stream; name=\"{$attachment['name']}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
            $body .= chunk_split(base64_encode(file_get_contents($attachment['path'])));
        }

        $body .= "\r\n--{$boundary}--";

        return $body;
    }

    private function formatAddress($address, $name = '')
    {
        return $name ? "{$name} <{$address}>" : $address;
    }

    private function extractEmail($address)
    {
        if (preg_match('/<(.+)>/', $address, $matches)) {
            return $matches[1];
        }
        return $address;
    }

    private function sendCommand($socket, $command)
    {
        fwrite($socket, $command . "\r\n");
        return $this->serverParse($socket, false);
    }

    private function serverParse($socket, $expected_response)
    {
        $response = '';
        while (substr($response, 3, 1) != ' ') {
            if (!($response = fgets($socket, 256))) {
                error_log('SMTP Server did not respond with expected code.');
                return false;
            }
        }

        if ($expected_response !== false && intval(substr($response, 0, 3)) !== $expected_response) {
            error_log("SMTP Server responded with unexpected code: {$response}");
            return false;
        }

        return true;
    }
}
