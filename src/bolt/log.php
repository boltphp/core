<?php

namespace bolt;
use \b;

use Monolog\Logger,
	Monglog\Handler\HandlerInterface,
	ReflectionClass
;

class log implements plugin\factory {

	public static function factory($parent, $config = []) {		
		if (!isset($config['name'])) {
			throw new \Exception("You must provide a name for the log.");
		}
		return new static($parent, $config['name'], $config);
	}

	private $_name;

	private $_app;

	private $_instance;

	public function __construct(application $app, $name, $config = []) {
		$this->_app = $app;
		$this->_name = $name;
		$this->_instance = new Logger($name);
	}

	public function __call($name, $args) {
		$map = [
			'debug' => 'pushDebug',
			'info' => 'pushInfo',
			'notice' => 'pushNotice',
			'warning' => 'pushWarning',
			'error' => 'pushError',
			'critical' => 'pushCritical',
			'alert' => 'pushAlert',
			'emergency' => 'pushEmergency'
		];

		if (isset($map[$name])) {
			$name = $map[$name];
		}

		if (method_exists($this->_instance, $name)) {
			return call_user_func_array([$this->_instance, $name], $args);
		}
		return null;
	}

	public function handler($class, $args = []) {
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
		if (!$class instanceof HandlerInterface) {
			throw new \Exception("Handler class does not implement HandlerInterface");
		}
		$this->pushHandler($class);
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
		$this->pushProcessor($class);
		return $this;
	} 

}