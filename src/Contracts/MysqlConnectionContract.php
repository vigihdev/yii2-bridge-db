<?php

declare(strict_types=1);

namespace VigihDev\Yii2BridgeDb\Contracts;

use Yiisoft\Db\Mysql\Connection;

interface MysqlConnectionContract
{

    public function getConnection(): Connection;
}
