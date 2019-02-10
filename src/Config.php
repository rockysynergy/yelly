<?php
namespace orq\php\yelly;
use Symfony\Component\Yaml\Yaml;

class Config {
    /**
     * @var array
     */
    private $value;

    public function __construct() {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configuration.yaml';

        if (!file_exists($configFile)) {
            throw new \Exception("Please create configuration.yaml at root directory");
        }

        $this->value = Yaml::parseFile($configFile);
    }

    /**
     * @param string $section To which the configuration value is returned
     * @return mixed
     */
    public function get($section) {
        if (array_key_exists($section, $this->value)) {
            return $this->value[$section];
        } else {
            throw new \Exception("No configuration is set for {$section}");
        }
    }
}