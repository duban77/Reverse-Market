<?php
/**
 * PHPMailer - PHP email creation and transport class (minified core)
 * Embedded directly - no installation needed
 */
class PHPMailer {
    public $Host       = 'smtp.gmail.com';
    public $Port       = 587;
    public $SMTPAuth   = true;
    public $SMTPSecure = 'tls';
    public $Username   = '';
    public $Password   = '';
    public $FromName   = 'Reverse Market';
    public $From       = '';
    public $CharSet    = 'UTF-8';
    public $isHTML     = true;
    public $Subject    = '';
    public $Body       = '';
    public $AltBody    = '';
    private $to        = [];
    private $error     = '';

    public function isSMTP() { return $this; }
    public function addAddress(string $addr, string $name = '') { $this->to[] = [$addr, $name]; }
    public function getError() { return $this->error; }

    public function send(): bool {
        if (empty($this->to)) { $this->error = 'No recipients'; return false; }
        [$toAddr, $toName] = $this->to[0];

        $socket = null;

        // Try SSL (port 465)
        $socket = @stream_socket_client("ssl://{$this->Host}:465", $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT, stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]));

        if (!$socket) {
            // Try TLS (port 587)
            $socket = @stream_socket_client("tcp://{$this->Host}:{$this->Port}", $errno, $errstr, 15);
            if (!$socket) { $this->error = "Cannot connect: $errstr ($errno)"; return false; }
            $this->r($socket); // banner
            $this->w($socket, "EHLO localhost"); $this->r($socket);
            $this->w($socket, "STARTTLS"); $this->r($socket);
            stream_socket_enable_crypto($socket, true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT|STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
        } else {
            $this->r($socket); // banner
        }

        $this->w($socket, "EHLO localhost"); $this->r($socket);
        $this->w($socket, "AUTH LOGIN"); $this->r($socket);
        $this->w($socket, base64_encode($this->Username)); $this->r($socket);
        $this->w($socket, base64_encode($this->Password));
        $auth = $this->r($socket);

        if (strpos($auth, '235') === false) {
            fclose($socket);
            $this->error = 'Auth failed. Check your Gmail app password.';
            return false;
        }

        $this->w($socket, "MAIL FROM:<{$this->From}>"); $this->r($socket);
        $this->w($socket, "RCPT TO:<{$toAddr}>"); $this->r($socket);
        $this->w($socket, "DATA"); $this->r($socket);

        $toHeader = $toName ? "=?UTF-8?B?".base64_encode($toName)."?= <{$toAddr}>" : $toAddr;
        $fromHeader = $this->FromName ? "=?UTF-8?B?".base64_encode($this->FromName)."?= <{$this->From}>" : $this->From;
        $subjectHeader = "=?UTF-8?B?".base64_encode($this->Subject)."?=";

        $contentType = $this->isHTML
            ? "Content-Type: text/html; charset={$this->CharSet}\r\nContent-Transfer-Encoding: base64"
            : "Content-Type: text/plain; charset={$this->CharSet}\r\nContent-Transfer-Encoding: base64";

        $msg  = "Date: " . date('r') . "\r\n";
        $msg .= "From: {$fromHeader}\r\n";
        $msg .= "To: {$toHeader}\r\n";
        $msg .= "Subject: {$subjectHeader}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "{$contentType}\r\n\r\n";
        $msg .= chunk_split(base64_encode($this->Body));

        $this->w($socket, $msg . "\r\n.");
        $sent = $this->r($socket);
        $this->w($socket, "QUIT");
        fclose($socket);

        if (strpos($sent, '250') === false) {
            $this->error = "Send failed: $sent";
            return false;
        }
        return true;
    }

    private function w($s, string $d): void { fwrite($s, $d . "\r\n"); }
    private function r($s): string {
        $r = '';
        while ($l = fgets($s, 512)) { $r .= $l; if (strlen($l) < 4 || $l[3] === ' ') break; }
        return $r;
    }
}
