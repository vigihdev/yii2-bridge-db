<?php

declare(strict_types=1);

namespace VigihDev\Yii2BridgeDb\Service;

use InvalidArgumentException;
use RuntimeException;
use VigihDev\Yii2BridgeDb\Contracts\{MysqlConnectionContract, ConnectionServiceContract};
use Yiisoft\Db\Mysql\Connection;

final class ConnectionService implements ConnectionServiceContract
{

    /**
     *
     * @param MysqlConnectionContract[] $dbConfigs
     * @return void
     */
    public function __construct(
        private readonly array $dbConfigs
    ) {}


    public function getConnection(string $name): Connection
    {
        $db = $this->getMysqlConnection($name);
        if (!$db) {
            throw new InvalidArgumentException("Connection {$name} tidak tersedia");
        }

        try {
            return $db->getConnection();
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getAvailableServiceNames(): array
    {
        return array_keys($this->dbConfigs);
    }

    public function hasServiceConnection(string $name): bool
    {
        return $this->getMysqlConnection($name) !== null;
    }

    private function getMysqlConnection(string $name): ?MysqlConnectionContract
    {
        return $this->dbConfigs[$name] ?? null;
    }
}
