<?php

namespace Amp\Mysql;

use Amp\Socket\ConnectContext;
use Amp\Sql\ConnectionConfig as SqlConnectionConfig;

final class ConnectionConfig extends SqlConnectionConfig
{
    const DEFAULT_PORT = 3306;
    const BIN_CHARSET = 45; // utf8mb4_general_ci

    const KEY_MAP = [
        'username' => 'user',
        'password' => 'pass',
        'database' => 'db',
        'dbname' => 'db',
        'compress' => 'useCompression',
        'charset' => 'charset',
    ];

    const DEFAULT_CHARSET = "utf8mb4";
    const DEFAULT_COLLATE = "utf8mb4_general_ci";

    /** @var bool */
    private $useCompression = false;
    /** @var bool */
    private $useLocalInfile = false;
    /** @var ConnectContext */
    private $context;
    /** @var string */
    private $charset = "utf8mb4";
    /** @var string  */
    private $collate = "utf8mb4_general_ci";
    /* @var string private key to use for sha256_password auth method */
    private $key;
    /** @var string|null */
    private $string;

    public static function fromString(string $connectionString, ConnectContext $context = null): self
    {
        $parts = self::parseConnectionString($connectionString);

        if (!isset($parts["host"])) {
            throw new \Error("Host must be provided in connection string");
        }

        return new self(
            $parts["host"],
            $parts["port"] ?? self::DEFAULT_PORT,
            $parts["user"] ?? null,
            $parts["password"] ?? null,
            $parts["db"] ?? null,
            $context,
            $parts['charset'] ?? self::DEFAULT_CHARSET,
            self::DEFAULT_COLLATE,
            $parts['compress'] ?? false,
            $parts['local_infile'] ?? false
        );
    }

    public function __construct(
        string $host,
        int $port = self::DEFAULT_PORT,
        string $user = null,
        string $password = null,
        string $database = null,
        ConnectContext $context = null,
        string $charset = self::DEFAULT_CHARSET,
        string $collate = self::DEFAULT_COLLATE,
        bool $useCompression = false,
        string $key = '',
        bool $useLocalInfile = false
    ) {
        parent::__construct($host, $port, $user, $password, $database);

        $this->context = $context ?? (new ConnectContext);
        $this->charset = $charset;
        $this->collate = $collate;
        $this->useCompression = $useCompression;
        $this->key = $key;
        $this->useLocalInfile = $useLocalInfile;
    }

    public function __clone()
    {
        $this->string = null;
    }

    public function getConnectionString(): string
    {
        if ($this->string !== null) {
            return $this->string;
        }

        $host = $this->getHost();

        $index = \strpos($host, ':');

        if ($index === false) {
            return $this->string = "tcp://$host:3306";
        }

        if ($index === 0) {
            return $this->string = "tcp://localhost:" . (int) \substr($host, 1);
        }

        list($host, $port) = \explode(':', $host, 2);

        return $this->string = "tcp://$host:" . (int) $port;
    }

    public function isCompressionEnabled(): bool
    {
        return $this->useCompression;
    }

    public function withCompression(): self
    {
        $new = clone $this;
        $new->useCompression = true;
        return $new;
    }

    public function withoutCompression(): self
    {
        $new = clone $this;
        $new->useCompression = false;
        return $new;
    }

    public function isLocalInfileEnabled(): bool
    {
        return $this->useLocalInfile;
    }

    public function withLocalInfile(): self
    {
        $new = clone $this;
        $new->useLocalInfile = true;
        return $new;
    }

    public function withoutLocalInfile(): self
    {
        $new = clone $this;
        $new->useLocalInfile = false;
        return $new;
    }

    public function getConnectContext()
    {
        return $this->context;
    }

    public function withConnectContext(ConnectContext $context): self
    {
        $new = clone $this;
        $new->context = $context;
        return $new;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getCollation(): string
    {
        return $this->collate;
    }

    public function withCharset(string $charset = self::DEFAULT_CHARSET, string $collate = self::DEFAULT_COLLATE): self
    {
        $new = clone $this;
        $new->charset = $charset;
        $new->collate = $collate;
        return $new;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function withKey(string $key): self
    {
        $new = clone $this;
        $new->key = $key;
        return $new;
    }
}
