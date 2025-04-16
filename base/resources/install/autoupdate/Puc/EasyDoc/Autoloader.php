<?php

if ( !class_exists('Puc_easydoc_Autoloader', false) ):

	class Puc_easydoc_Autoloader {
		private $prefix = '';
		private $rootDir = '';
		private $libraryDir = '';

		public function __construct() {
			$this->rootDir = dirname(__FILE__) . '/';
			$nameParts = explode('_', __CLASS__, 3);
			$this->prefix = $nameParts[0] . '_' . $nameParts[1] . '_';

			$this->libraryDir = realpath($this->rootDir . '../..') . '/';

			spl_autoload_register([$this, 'autoload']);
		}

		public function autoload($className) {
			if (strpos($className, $this->prefix) === 0) {
				$path = substr($className, strlen($this->prefix));
				$path = str_replace('_', '/', $path);
				$path = $this->rootDir . $path . '.php';

				if (file_exists($path)) {
					/** @noinspection PhpIncludeInspection */
					include $path;
				}
			}
		}
	}

endif;