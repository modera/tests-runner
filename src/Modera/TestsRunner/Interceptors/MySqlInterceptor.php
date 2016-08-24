<?php

namespace Modera\TestsRunner\Interceptors;

use Modera\TestsRunner\BaseInterceptor;

/**
 * Creates a MySQL database before running a test-suite and drops it afterwards.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class MySqlInterceptor extends BaseInterceptor
{
    const FIRST_ATTEMPT = 'first_attempt';
    const PROGRESS_INCREASE = 'progress_increase';
    const DB_FAIL = 'db_fail';
    const CONNECTED = 'connected';

    /**
     * @var callable
     */
    private $configProvider;

    /**
     * @var callable
     */
    private $reportingCallback;

    /**
     * @var \mysqli
     */
    private $db;

    /**
     * @param callable $configProvider
     * @param callable $reportingCallback
     */
    public function __construct(callable $configProvider, callable $reportingCallback = null)
    {
        $this->configProvider = $configProvider;
        $this->reportingCallback = $reportingCallback;

        if (!$this->reportingCallback) {
            $this->reportingCallback = function($type, array $args = array()) {
                switch ($type) {
                    case self::FIRST_ATTEMPT:
                        echo 'Attempting to connect to database ';

                        break;

                    case self::PROGRESS_INCREASE:
                        echo ".";
                        sleep(1);

                        break;

                    case self::CONNECTED:
                        echo "\n";

                        break;

                    case self::DB_FAIL:
                        echo "\n\n";
                        throw new \RuntimeException('Unable to connect to database', null, $args['exception']);

                        break;
                }
            };
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onEnter($dir, array $composerJson)
    {
        if (!isset($composerJson['name'])) {
            return;
        }

        // deferred database initialization
        if (!$this->db) {
            $config = call_user_func($this->configProvider);
            $this->validateConfig($config);

            $this->db = $this->connectToDb($config);
        }

        $this->db->query("CREATE DATABASE ".$this->formatTableName($composerJson['name']));
    }

    private function validateConfig(array $config)
    {
        // TODO
    }

    private function connectToDb(array $config, $currentAttempt = 0) {
        try {
            mysqli_report(MYSQLI_REPORT_STRICT);
            $db = new \mysqli($config['host'], $config['user'], $config['password']);

            call_user_func($this->reportingCallback, self::CONNECTED);

            return $db;
        } catch (\Exception $e) {
            if ($currentAttempt < $config['attempts']) {
                if (0 == $currentAttempt) {
                    call_user_func($this->reportingCallback, self::FIRST_ATTEMPT);
                }

                call_user_func($this->reportingCallback, self::PROGRESS_INCREASE);

                return $this->connectToDb($config, 1+$currentAttempt);
            } else {
                call_user_func($this->reportingCallback, self::DB_FAIL, array('exception' => $e));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onLeave($dir, array $composerJson)
    {
        if (!isset($composerJson['name'])) {
            return;
        }

        $this->db->query("DROP DATABASE ".$this->formatTableName($composerJson['name']));
    }

    /**
     * @param string $packageName
     *
     * @return string
     */
    private function formatTableName($packageName)
    {
        $segments = [];
        if (strpos($packageName, '/')) {
            list ($vendor, $packageName) = explode('/', $packageName);

            $segments = array_merge([$vendor], explode('-', $packageName));
        } else {
            $segments = explode('-', $packageName);
        }

        $tableName = implode('_', $segments);

        return $tableName;
    }
}