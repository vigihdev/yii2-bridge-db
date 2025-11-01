<?php

declare(strict_types=1);

namespace VigihDev\Yii2BridgeDb\Contracts;

use Yiisoft\Db\Mysql\Connection;

interface ConnectionServiceContract
{
    public function getConnection(string $name): Connection;
    public function getAvailableServiceNames(): array;
    public function hasServiceConnection(string $name): bool;
}
