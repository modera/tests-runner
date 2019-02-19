<?php

namespace Modera\TestsRunner\Interceptors;

use PHPUnit\Framework\TestCase;

class MysqlInterceptorUT extends MySqlInterceptor
{
    public $db;

    public $givenDbConfig;

    // override
    protected function createDatabaseConnection(array $config)
    {
        $this->givenDbConfig = $config;

        return $this->db;
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class MySqlInterceptorTest extends TestCase
{
    public function testHowWellItWorks()
    {
        $databaseConfig = array(
            'host' => 'foo_host',
            'user' => 'foo_user',
            'password' => 'foo_pwd',
            'port' => 1234,
            'attempts' => 5,
        );

        $intr = new MysqlInterceptorUT(
            function () use ($databaseConfig) {
                return $databaseConfig;
            },
            function ($type, array $args = array()) {
            }
        );

        $intr->db = \Phake::mock(\mysqli::class);

        $composerJson = array(
            'name' => 'modera/foo-bundle',
        );

        $intr->onEnter('foo-dir', $composerJson);

        $this->assertTrue(is_array($intr->givenDbConfig));
        $this->assertEquals($databaseConfig, $intr->givenDbConfig);

        \Phake::verify($intr->db)->query('CREATE DATABASE modera_foo_bundle');

        $intr->onLeave('foo-dir', $composerJson);

        \Phake::verify($intr->db)->query('DROP DATABASE modera_foo_bundle');
    }
}
