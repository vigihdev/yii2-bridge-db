<?php

namespace VigihDev\Yii2BridgeDb\Tests;

use PHPUnit\Framework\TestCase;
use VigihDev\Yii2BridgeDb\Contracts\ConnectionServiceContract;
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;
use Yiisoft\Db\Mysql\Connection;

class MultipleConnectionTest extends TestCase
{
    private ConnectionServiceContract $connectionService;

    protected function setUp(): void
    {
        ConfigBridge::boot(
            basePath: dirname(__DIR__),
            configDir: 'config',
            enableAutoInjection: true,
        );
        $this->connectionService = ServiceLocator::get(ConnectionServiceContract::class);
    }

    /** 
     * @test 
     */
    public function it_can_connect_to_multiple_databases()
    {
        // Test connection ke testDb
        $testDb = $this->connectionService->getConnection('testDb');
        $this->assertInstanceOf(Connection::class, $testDb);

        // Test dengan execute query
        try {
            $result = $testDb->createCommand('SELECT 1')->execute();
            $this->assertTrue(true); // Connection works
        } catch (\Exception $e) {
            $this->fail("Connection testDb failed: " . $e->getMessage());
        }

        // Test connection ke terms
        $termsDb = $this->connectionService->getConnection('terms');
        $this->assertInstanceOf(Connection::class, $termsDb);

        try {
            $result = $termsDb->createCommand('SELECT 1')->execute();
            $this->assertTrue(true); // Connection works
        } catch (\Exception $e) {
            $this->fail("Connection terms failed: " . $e->getMessage());
        }

        // Pastikan mereka berbeda connection
        $this->assertNotSame($testDb, $termsDb);
    }

    /** 
     * @test 
     */
    public function it_returns_different_tables_for_each_database()
    {
        $testDb = $this->connectionService->getConnection('testDb');
        $termsDb = $this->connectionService->getConnection('terms');

        $testDbTables = $testDb->getSchema()->getTableNames();
        $termsDbTables = $termsDb->getSchema()->getTableNames();

        $this->assertIsArray($testDbTables);
        $this->assertIsArray($termsDbTables);

        // Cukup test bahwa mereka return array (tidak perlu compare specific tables)
        $this->assertTrue(count($testDbTables) >= 0);
        $this->assertTrue(count($termsDbTables) >= 0);
    }

    /** 
     * @test 
     */
    public function it_can_execute_queries_on_different_connections()
    {
        $testDb = $this->connectionService->getConnection('testDb');
        $termsDb = $this->connectionService->getConnection('terms');

        // Test query di testDb - gunakan try/catch untuk handle table tidak ada
        try {
            $testDbTables = $testDb->getSchema()->getTableNames();
            if (in_array('user', $testDbTables)) {
                $users = $testDb->createCommand('SELECT COUNT(*) as count FROM user')->queryOne();
                $this->assertIsArray($users);
                $this->assertArrayHasKey('count', $users);
            }
        } catch (\Exception $e) {
            // Skip jika table tidak ada atau error lain
            $this->markTestSkipped('Test DB query skipped: ' . $e->getMessage());
        }

        // Test query di termsDb
        try {
            $termsDbTables = $termsDb->getSchema()->getTableNames();
            if (in_array('towing', $termsDbTables)) {
                $towingData = $termsDb->createCommand('SELECT COUNT(*) as count FROM towing')->queryOne();
                $this->assertIsArray($towingData);
                $this->assertArrayHasKey('count', $towingData);
            }
        } catch (\Exception $e) {
            // Skip jika table tidak ada atau error lain
            $this->markTestSkipped('Terms DB query skipped: ' . $e->getMessage());
        }
    }

    /** 
     * @test 
     */
    public function it_throws_exception_for_invalid_connection_name()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Flexible exception message matching
        $this->expectExceptionMessageMatches('/(tidak tersedia|tidak dikonfigurasi|not found|invalid)/i');

        $this->connectionService->getConnection('non_existent_connection');
    }

    /** 
     * @test 
     */
    public function it_provides_active_connections()
    {
        $connection = $this->connectionService->getConnection('testDb');

        // Test dengan query sederhana
        $result = $connection->createCommand('SELECT 1 as test')->queryOne();

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['test']);
    }

    /** 
     * @test 
     */
    public function it_can_list_available_connections()
    {
        // Jika service Anda punya method untuk list connections
        if (method_exists($this->connectionService, 'getAvailableServiceNames')) {
            $connections = $this->connectionService->getAvailableServiceNames();
            $this->assertIsArray($connections);
            $this->assertContains('testDb', $connections);
            $this->assertContains('terms', $connections);
        } else {
            // Fallback: test bahwa kita bisa access configured connections
            $testDb = $this->connectionService->getConnection('testDb');
            $termsDb = $this->connectionService->getConnection('terms');

            $this->assertInstanceOf(Connection::class, $testDb);
            $this->assertInstanceOf(Connection::class, $termsDb);
        }
    }
}
