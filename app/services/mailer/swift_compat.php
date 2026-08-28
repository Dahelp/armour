<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Temporary source-compatible bridge for the project's old mail call sites.
 * Delivery and MIME handling are provided entirely by Symfony Mailer.
 */
final class Swift_SmtpTransport
{
    private string $username = '';
    private string $password = '';

    public function __construct(
        private readonly string $host = 'localhost',
        private readonly int $port = 25,
        private readonly ?string $encryption = null
    ) {
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function dsn(): string
    {
        $scheme = strtolower((string)$this->encryption) === 'ssl' ? 'smtps' : 'smtp';
        $credentials = '';
        if ($this->username !== '') {
            $credentials = rawurlencode($this->username) . ':' . rawurlencode($this->password) . '@';
        }

        $query = strtolower((string)$this->encryption) === 'tls' ? '?require_tls=true' : '';

        return sprintf('%s://%s%s:%d%s', $scheme, $credentials, $this->host, $this->port, $query);
    }
}

final class Swift_Attachment
{
    private ?string $filename = null;

    private function __construct(private readonly string $path)
    {
    }

    public static function fromPath(string $path): self
    {
        return new self($path);
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function filename(): ?string
    {
        return $this->filename;
    }
}

final class Swift_Message
{
    private Email $email;

    public function __construct(string $subject = '')
    {
        $this->email = (new Email())->subject($subject);
    }

    public function setFrom(string|array $addresses): self
    {
        $this->email->from(...$this->normaliseAddresses($addresses));

        return $this;
    }

    public function setTo(string|array $addresses): self
    {
        $this->email->to(...$this->normaliseAddresses($addresses));

        return $this;
    }

    public function setReplyTo(string|array $addresses): self
    {
        $this->email->replyTo(...$this->normaliseAddresses($addresses));

        return $this;
    }

    public function setBody(string $body, string $contentType = 'text/plain'): self
    {
        if (strtolower($contentType) === 'text/html') {
            $this->email->html($body);
        } else {
            $this->email->text($body);
        }

        return $this;
    }

    public function attach(Swift_Attachment $attachment): self
    {
        $this->email->attachFromPath($attachment->path(), $attachment->filename());

        return $this;
    }

    public function email(): Email
    {
        return $this->email;
    }

    /** @return list<Address> */
    private function normaliseAddresses(string|array $addresses): array
    {
        if (is_string($addresses)) {
            return [new Address($addresses)];
        }

        $result = [];
        foreach ($addresses as $key => $value) {
            $result[] = is_string($key)
                ? new Address($key, (string)$value)
                : new Address((string)$value);
        }

        return $result;
    }
}

final class Swift_Plugins_AntiFloodPlugin
{
    public function __construct(
        public readonly int $threshold = 100,
        public readonly int $sleep = 0
    ) {
    }
}

final class Swift_Mailer
{
    private SymfonyMailer $mailer;
    private ?Swift_Plugins_AntiFloodPlugin $antiFlood = null;
    private int $sent = 0;

    public function __construct(Swift_SmtpTransport $transport)
    {
        $this->mailer = new SymfonyMailer(Transport::fromDsn($transport->dsn()));
    }

    public function registerPlugin(object $plugin): void
    {
        if ($plugin instanceof Swift_Plugins_AntiFloodPlugin) {
            $this->antiFlood = $plugin;
        }
    }

    public function send(Swift_Message $message): int
    {
        $this->mailer->send($message->email());
        ++$this->sent;

        if ($this->antiFlood !== null
            && $this->antiFlood->threshold > 0
            && $this->sent % $this->antiFlood->threshold === 0
            && $this->antiFlood->sleep > 0
        ) {
            sleep($this->antiFlood->sleep);
        }

        return 1;
    }
}
