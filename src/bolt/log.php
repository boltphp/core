<?php

namespace bolt;
use \b;

use Monolog\Logger,
	Monglog\Handler\HandlerInterface,
	ReflectionClass
;

class log implements plugin\singleton {


	public static function factory($parent, $config = []) {
		return new log($parent, $config);
	}

	private $_name;

	private $_app;

	private $_instance;

	public function __construct(application $app, $config = []) {
		if (!isset($config['name'])) {
			throw new \Exception("You must provide a name for the log.");
		}
		$this->_app = $app;
		$this->_name = $config['name'];
		$this->_instance = new Logger($this->_name);
	}

	public function getInstance() {
		return $this->_instance;
	}

	public function level($name) {
		$name = strtoupper($name);
		return constant("\Monolog\Logger::{$name}");
	}

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

	public function handler($class, $level = null, $args = []) {
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