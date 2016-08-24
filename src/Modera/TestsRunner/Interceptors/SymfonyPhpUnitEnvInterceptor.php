<?php

namespace Modera\TestsRunner\Interceptors;

use Modera\TestsRunner\BaseInterceptor;

/**
 * If given package has phpunit.xml/phpunit.xml.dist then this interceptor will scan its php/server[]
 * (PHPUnit uses them to update $_SERVER) properties and update $_SERVER as well, out of the box
 * PHPUnit will pay no attention to nested phpunit.xml files.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SymfonyPhpUnitEnvInterceptor extends BaseInterceptor
{
    /**
     * @var array
     */
    private $vars = [];

    /**
     * {@inheritdoc}
     */
    public function onEnter($dir, array $composerJson)
    {
        if (file_exists("$dir/phpunit.xml")) {
            $phpUnitXmlPath = "$dir/phpunit.xml";
        } elseif (file_exists("$dir/phpunit.xml.dist")) {
            $phpUnitXmlPath = "$dir/phpunit.xml.dist";
        } else {
            return;
        }

        $this->vars = [];

        // setting env variables by scanning phpunit's php/server[] values
        $xml = new \SimpleXMLElement(file_get_contents($phpUnitXmlPath));
        foreach ($xml as $child) {
            /* @var \SimpleXMLElement $child */

            if ($child->getName() == 'php') {
                foreach ($child->children() as $phpChild) {
                    /* @var \SimpleXMLElement $phpChild */
                    if ($phpChild->getName() == 'server') {
                        $attrs = array();
                        foreach ($phpChild->attributes() as $name=>$value) {
                            $attrs[$name] = (string)$value;
                        }

                        if (isset($attrs['name']) && isset($attrs['value'])) {
                            // transforming paths like "./Tests/Fixtures/App/app" to "path-to-bundle/Tests/Fixtures/App/app"
                            if ('KERNEL_DIR' == $attrs['name']) {
                                if (substr($attrs['value'], 0, strlen('./')) == './') {
                                    $attrs['value'] = substr($attrs['value'], strlen('./'));
                                }

                                $attrs['value'] = $dir.DIRECTORY_SEPARATOR.$attrs['value'];
                            }

                            $this->vars[] = $attrs['name'];

                            $_SERVER[$attrs['name']] = $attrs['value'];
                        }
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onLeave($dir, array $composerJson)
    {
        foreach ($this->vars as $name) {
            if (isset($_SERVER[$name])) {
                unset($_SERVER[$name]);
            }
        }
    }
}