<?php

namespace Kagency\CouchdbEndpoint;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\FileLocator;

class Container
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public $container;

    /**
     * @var string
     */
    private $basedir;

    /**
     * @param string $configuration
     * @param string|null $basedir
     * @param string|null $cachedir
     */
    public function __construct($basedir = null)
    {
        $this->basedir = $basedir ?: __DIR__ . '/../../config';
    }

    /**
     * Get service from container
     *
     * @param $identifier
     * @return mixed
     */
    public function get($identifier)
    {
        $this->compile();
        return $this->container->get($identifier);
    }

    /**
     * @return void
     */
    private function compile()
    {
        if ($this->container !== null) {
            return;
        }

        $this->container = new ContainerBuilder();

        $this->parser = new \Symfony\Component\Yaml\Parser();
        $configuration = $this->parser->parse(file_get_contents($this->basedir . '/config.yml')) ?: array();
        foreach ($configuration as $name => $value) {
            $this->container->setParameter($name, $value);
        }

        $loader = new XmlFileLoader(
            $this->container,
            new FileLocator($this->basedir)
        );
        $loader->load('services.xml');

        $this->container->compile();
    }
}
