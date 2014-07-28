<?php

namespace bolt;
use \b;

use Monolog\Logger,
	Monglog\Handler\HandlerInterface,
	ReflectionClass
;

class log implements plugin\singleton {

	/**
	 * create a log instance
	 * 
	 * @param  bolt\application $parent
	 * @param  array $config
	 * @return \bolt\log
	 */
	public static function factory($parent, $config = []) {
		return new log($parent, $config);
	}

	/**
	 * name of log instance
	 * 
	 * @var string
	 */
	private $_name;

	/**
	 * application
	 * 
	 * @var bolt\application
	 */
	private $_app;

	/**
	 * monolog instance
	 * 
	 * @var Monolog\Logger
	 */
	private $_instance;

	/**
	 * Constructor
	 * 
	 * @param bolt\application $app
	 * @param array $config 
	 */
	public function __construct(application $app, array $config = []) {
		if (!isset($config['name'])) {
			throw new \Exception("You must provide a name for the log.");
		}
		$this->_app = $app;
		$this->_name = $config['name'];
		$this->_instance = new Logger($this->_name);
	}


	/**
	 * get the Monolog instance
	 * 
	 * @return Monoglog\Logger
	 */
	public function getInstance() {
		return $this->_instance;
	}


	/**
	 * return the instance name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}


	/**
	 * return a logger instance constant
	 * 
	 * @param  string $name 
	 * 
	 * @return Mongolog\Logger::$name
	 */
	public function level($name) {
		$name = strtoupper($name);
		$ref = "\Monolog\Logger::{$name}";
		return defined($ref) ? constant($ref) : null;
	}


	/**
	 * passthrough to monolog\logger
	 * 
	 * @param  string $name
	 * @param  array $args
	 * 
	 * @return mixed
	 */
	public function __call($name, $args) {
		$map = [
			'debug' => 'addDebug',
			'info' => 'addInfo',
			'notice' => 'addNotice',
			'warning' => 'addWarning',
			'error' => 'addError',
			'critical' => 'addCritical',
			'alert' => 'addAlert',
			'emergency' => 'addEmergency'
		];

		if (isset($map[$name])) {
			$name = $map[$name];
		}

		if (method_exists($this->_instance, $name)) {
			try {
				return call_user_func_array([$this->_instance, $name], $args);
			}
			catch (\Exception $e) { error_log("LOG ERROR: {$e->getMessage()}"); }
		}
		return null;
	}


	/**
	 * add a handler to the Mongolog\Logger instnace
	 * 
	 * @param  string $class
	 * @param  mixed $level
	 * @param  array $args 
	 * 
	 * @return self
	 */
	public function handler($class, $level = null, array $args = []) {
		if (is_string($class)) {
			$map = [
				'stream' => 'StreamHandler',
				'file' => 'RotatingFileHandler',
				'syslog' => 'SyslogHandler',
				'errorlog' => 'ErrorLogHandler'
			];
			if (isset($map[$class])) {
				$class = $map[$class];
			}
			if (!class_exists($class, true)) {
				$class = "Monolog\\Handler\\{$class}";
			}
			$ref = new ReflectionClass($class);
			$class = $ref->newInstanceArgs($args);
		}

		if (!in_array('Monolog\Handler\HandlerInterface', class_implements($class))) {
			throw new \Exception("Handler class does not implement HandlerInterface");
		}

		$this->_instance->pushHandler($class, $level);
		return $this;
	}


	/**
	 * add a processor to Mongolog\Logger instance
	 * 
	 * @param  string $class
	 * @param  array $args 
	 * 
	 * @return self
	 */
	public function processor($class, $args = []) {
		if (is_string($class)) {
			$map = [
 				"introspection" => "IntrospectionProcessor",
 				"web" => "WebProcessor",
 				"memuse" => "MemoryUsageProcessor",
 				"mempeak" => "MemoryPeakUsageProcessor",
 				"pid" => "ProcessIdProcessor",
 				"uid" => "UidProcessor",
 				"git" => "GitProcessor",
 				"tag" => "TagProcessor",
			];
			if (isset($map[$class])) {
				$class = $map[$class];
			}
			if (!class_exists($class, true)) {
				$class = "Monolog\\Processor\\{$class}";
			}
			$ref = new ReflectionClass($class);
			$class = $ref->newInstanceArgs($args);
		}
		$this->_instance->pushProcessor($class);
		return $this;
	}

}