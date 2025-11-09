<?php

declare(strict_types=1);

namespace VigihDev\Yii2BridgeDb\Connection;

use RuntimeException;
use Stringable;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;
use VigihDev\SymfonyBridge\Config\AttributeInjection\{DependencyInjector, Inject};
use VigihDev\Yii2BridgeDb\Contracts\MysqlConnectionContract;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;

final class MysqlEncryptorConnection implements Stringable, MysqlConnectionContract
{

    public function __construct(
        public readonly string $dbname = '',
        public readonly string $username = '',
        public readonly string $password = '',
        public readonly string $host = '127.0.0.1',
        public readonly string $driver = 'mysql',
        public readonly string $port = '3306',
        public readonly array $options = [
            'charset' => 'utf8mb4',
        ],
        #[Inject(EnvironmentEncryptorServiceContract::class)]
        private ?EnvironmentEncryptorServiceContract $encryptor = null
    ) {
        DependencyInjector::inject($this);
    }


    public function getConnection(): Connection
    {

        try {

            $dsn = $this->__toDsnDecrypt();
            $driver = new Driver(
                dsn: $dsn,
                username: $this->decrypt($this->username),
                password: $this->decrypt($this->password),
                attributes: $this->options
            );

            $connection = new Connection(
                driver: $driver,
                schemaCache: new SchemaCache(new ArrayCache())
            );

            return $connection;
        } catch (\RuntimeException $e) {
            throw new RuntimeException("Gagal Connection ke {$this->dbname}");
        }
    }

    private function decrypt(string $value): string
    {
        return $this->encryptor->isEncrypted($value) ? $this->encryptor->decrypt($value) : $value;
    }

    private function __toDsnDecrypt(): string
    {
        $dsn = "$this->driver:host={$this->decrypt($this->host)}";

        if ($this->dbname !== '') {
            $dsn .= ";dbname={$this->decrypt($this->dbname)}";
        }

        if ($this->port !== '') {
            $dsn .= ";port={$this->decrypt($this->port)}";
        }

        foreach ($this->options as $key => $value) {
            $dsn .= ";$key=$value";
        }

        return $dsn;
    }

    public function __toString(): string
    {
        $dsn = "$this->driver:host=$this->host";

        if ($this->dbname !== '') {
            $dsn .= ";dbname=$this->dbname";
        }

        if ($this->port !== '') {
            $dsn .= ";port=$this->port";
        }

        foreach ($this->options as $key => $value) {
            $dsn .= ";$key=$value";
        }

        return $dsn;
    }
}
