<?php

namespace Modera\TestsRunner\Interceptors;

use Modera\TestsRunner\BaseInterceptor;

/**
 * Allows to export provided in constructor variables into $_SERVER if they are not defined yet.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class ServerEnvExportingInterceptor extends BaseInterceptor
{
    /**
     * @var array
     */
    private $envVars = array();

    /**
     * @param array $envVars
     */
    public function __construct(array $envVars = array())
    {
        $this->envVars = $envVars;
    }

    /**
     * {@inheritdoc}
     */
    public function onEnter($dir, array $composerJson)
    {
        global $_SERVER;

        foreach ($this->envVars as $name => $value) {
            if (!isset($_SERVER[$name])) {
                $_SERVER[$name] = $value;
            }
        }
    }
}
