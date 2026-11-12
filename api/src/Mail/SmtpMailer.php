<?php

declare(strict_types=1);

namespace SlavaAds\Mail;

/**
 * Minimal SMTP client (STARTTLS/SSL + AUTH LOGIN) for relays such as Brevo.
 * Speaks just enough SMTP to deliver short transactional messages.
 */
final class SmtpMailer implements Mailer
{
    /** @var resource|null */
    private $socket = null;

    public function __construct(
        private readonly SmtpSettings $settings,
        private readonly int $timeoutSeconds = 12,
    ) {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if (!$this->settings->isConfigured()) {
            return false;
        }

        try {
            return $this->deliver($to, $subject, $body);
        } catch (\RuntimeException $e) {
            error_log('[mailer] ' . $e->getMessage());
            return false;
        } finally {
            $this->close();
        }
    }

    private function deliver(string $to, string $subject, string $body): bool
    {
        $this->connect();
        $this->expect(220);

        $this->command('EHLO localhost', 250);

        if ($this->settings->secure === 'tls') {
            $this->command('STARTTLS', 220);
            $this->enableCrypto();
            $this->command('EHLO localhost', 250);
        }

        $this->command('AUTH LOGIN', 334);
        $this->command(base64_encode($this->settings->username), 334);
        $this->command(base64_encode($this->settings->password), 235);

        $this->command('MAIL FROM:<' . $this->settings->fromEmail . '>', 250);
        $this->command('RCPT TO:<' . $to . '>', 250, 251);
        $this->command('DATA', 354);

        $this->write($this->buildMessage($to, $subject, $body));
        $this->expect(250);
        $this->write('QUIT');

        return true;
    }

    private function connect(): void
    {
        $prefix = $this->settings->secure === 'ssl' ? 'ssl://' : 'tcp://';
        $endpoint = $prefix . $this->settings->host . ':' . $this->settings->port;

        $socket = @stream_socket_client(
            $endpoint,
            $errno,
            $errstr,
            $this->timeoutSeconds,
            STREAM_CLIENT_CONNECT,
        );
        if ($socket === false) {
            throw new \RuntimeException("connect failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($socket, $this->timeoutSeconds);
        $this->socket = $socket;
    }

    private function enableCrypto(): void
    {
        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new \RuntimeException('STARTTLS negotiation failed');
        }
    }

    private function command(string $line, int ...$expectedCodes): void
    {
        $this->write($line);
        $this->expect(...$expectedCodes);
    }

    private function write(string $line): void
    {
        fwrite($this->socket, $line . "\r\n");
    }

    private function expect(int ...$codes): void
    {
        $response = $this->read();
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new \RuntimeException('unexpected SMTP reply: ' . trim($response));
        }
    }

    private function read(): string
    {
        $data = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $data .= $line;
            // Final line of a multiline reply has a space at index 3.
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    private function buildMessage(string $to, string $subject, string $body): string
    {
        $headers = [
            'From: "' . $this->settings->fromName . '" <' . $this->settings->fromEmail . '>',
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: slava-ads-smtp',
        ];

        // A leading dot on a body line must be escaped per RFC 5321.
        $escapedBody = preg_replace('/^\./m', '..', $body) ?? $body;

        return implode("\r\n", $headers) . "\r\n\r\n" . $escapedBody . "\r\n.";
    }

    private function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}
