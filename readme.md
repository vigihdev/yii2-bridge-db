# Yii2 Bridge DB

Database bridge component for Yii2 framework dengan service container pattern untuk multiple database connections.

## ✨ Features

- ✅ Multiple database connections
- ✅ Service container integration
- ✅ Environment variables configuration
- ✅ Automatic connection management
- ✅ Ready-to-use connections
- ✅ Comprehensive testing

## 🚀 Installation

```bash
composer require vigihdev/yii2-bridge-db
```

## 📋 Requirements

- PHP 8.1+
- Yii2 Database Components

## 🔧 Configuration

### 1. Environment Variables (.env)

```env
APP_ENV=local

# Primary Database
PRIMARY_DB_HOST=localhost
PRIMARY_DB_NAME=myapp_primary
PRIMARY_DB_USER=myapp_user
PRIMARY_DB_PASSWORD=secure_password
PRIMARY_DB_PORT=3306

# Analytics Database
ANALYTICS_DB_HOST=localhost
ANALYTICS_DB_NAME=myapp_analytics
ANALYTICS_DB_USER=analytics_user
ANALYTICS_DB_PASSWORD=analytics_pass
ANALYTICS_DB_PORT=3306

# Logging Database
LOGGING_DB_HOST=localhost
LOGGING_DB_NAME=myapp_logs
LOGGING_DB_USER=logs_user
LOGGING_DB_PASSWORD=logs_pass
LOGGING_DB_PORT=3306
```

### 2. Service Configuration (services.yaml)

```yaml
services:
  primary.db:
    public: false
    class: 'VigihDev\Yii2BridgeDb\Connection\MysqlConnection'
    arguments:
      $dbname: "%env(PRIMARY_DB_NAME)%"
      $username: "%env(PRIMARY_DB_USER)%"
      $password: "%env(PRIMARY_DB_PASSWORD)%"
      $host: "%env(PRIMARY_DB_HOST)%"
      $port: "%env(PRIMARY_DB_PORT)%"

  analytics.db:
    public: false
    class: 'VigihDev\Yii2BridgeDb\Connection\MysqlConnection'
    arguments:
      $dbname: "%env(ANALYTICS_DB_NAME)%"
      $username: "%env(ANALYTICS_DB_USER)%"
      $password: "%env(ANALYTICS_DB_PASSWORD)%"
      $host: "%env(ANALYTICS_DB_HOST)%"
      $port: "%env(ANALYTICS_DB_PORT)%"

  logging.db:
    public: false
    class: 'VigihDev\Yii2BridgeDb\Connection\MysqlConnection'
    arguments:
      $dbname: "%env(LOGGING_DB_NAME)%"
      $username: "%env(LOGGING_DB_USER)%"
      $password: "%env(LOGGING_DB_PASSWORD)%"
      $host: "%env(LOGGING_DB_HOST)%"
      $port: "%env(LOGGING_DB_PORT)%"

  VigihDev\Yii2BridgeDb\Contracts\ConnectionServiceContract:
    class: 'VigihDev\Yii2BridgeDb\Services\ConnectionService'
    arguments:
      $connections:
        primary: "@primary.db"
        analytics: "@analytics.db"
        logging: "@logging.db"
```

## 💻 Usage

### Basic Usage

```php
<?php

use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;
use VigihDev\Yii2BridgeDb\Contracts\ConnectionServiceContract;

require __DIR__ . '/vendor/autoload.php';

ConfigBridge::boot(__DIR__);

// Get connection service
$connectionService = ServiceLocator::get(ConnectionServiceContract::class);

// Use different database connections
$primaryDb = $connectionService->getConnection('primary');    // Main application data
$analyticsDb = $connectionService->getConnection('analytics'); // Analytics data
$loggingDb = $connectionService->getConnection('logging');    // Logs data

// Execute queries on different databases
$users = $primaryDb->createCommand("SELECT * FROM users")->queryAll();
$stats = $analyticsDb->createCommand("SELECT * FROM user_metrics")->queryAll();
$logs = $loggingDb->createCommand("SELECT * FROM system_logs")->queryAll();
```

### Real-world Example

```php
<?php
// app.php

use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;
use VigihDev\Yii2BridgeDb\Contracts\ConnectionServiceContract;

require __DIR__ . '/vendor/autoload.php';

ConfigBridge::boot(__DIR__);

try {
    $connection = ServiceLocator::get(ConnectionServiceContract::class);

    // Primary database for user operations
    $primaryDb = $connection->getConnection('primary');
    $userTables = $primaryDb->getSchema()->getTableNames();
    echo "Primary DB Tables: " . implode(', ', $userTables) . PHP_EOL;

    // Analytics database for reports
    $analyticsDb = $connection->getConnection('analytics');
    $reportData = $analyticsDb->createCommand("SELECT * FROM daily_reports")->queryAll();

    // Logging database for audit trails
    $loggingDb = $connection->getConnection('logging');
    $loggingDb->createCommand()->insert('audit_log', [
        'action' => 'app_start',
        'timestamp' => date('Y-m-d H:i:s')
    ])->execute();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
```

## 🎯 Use Cases

### Multi-tenant Architecture

```php
$tenant1Db = $connectionService->getConnection('tenant_1');
$tenant2Db = $connectionService->getConnection('tenant_2');
```

### Microservices Data Isolation

```php
$usersDb = $connectionService->getConnection('users_service');
$ordersDb = $connectionService->getConnection('orders_service');
$paymentsDb = $connectionService->getConnection('payments_service');
```

### Read/Write Separation

```php
$writeDb = $connectionService->getConnection('primary_write');
$readDb = $connectionService->getConnection('primary_read');
```

## 🧪 Testing

Package ini sudah teruji dengan comprehensive tests:

```bash
composer test
```

**Test Results:**

```
Tests: 7, Assertions: 21, PHPUnit Deprecations: 1.
```

## 🏗️ Architecture

```
ConnectionServiceContract
         ↓
ConnectionService
         ↓
MysqlConnection → Yiisoft\Db\Mysql\Connection
```

## 📝 License

MIT License

## 🤝 Support

Email: vigihdev@gmail.com

---
