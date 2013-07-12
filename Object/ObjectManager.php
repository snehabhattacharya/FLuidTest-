<?php

namespace TYPO3\Fluid\Object;

/**
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * http://groups.google.com/group/php-standards/web/final-proposal
 *
 *     // Example which loads classes for the Doctrine Common package in the
 *     // Doctrine\Common namespace.
 *     $classLoader = new SplClassLoader('Doctrine\Common', '/path/to/doctrine');
 *     $classLoader->register();
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 */
class ObjectManager implements \TYPO3\Fluid\Object\ObjectManagerInterface
{
	private $_fileExtension = '.php';
	private $_namespace;
	private $_includePath;
	private $_namespaceSeparator = '\\';
	private $_implementations = array(
		'TYPO3\\Fluid\\Object\\ObjectManagerInterface' => 'TYPO3\\Fluid\\Object\\ObjectManager'
	);
	private $_singletons = array();

	/**
	 * Creates a new <tt>SplClassLoader</tt> that loads classes of the
	 * specified namespace.
	 *
	 * @param string $ns The namespace to use.
	 */
	public function __construct($ns = NULL, $includePath = NULL) {
		$this->_namespace = $ns !== NULL ? $ns : 'TYPO3';
		$this->_includePath = $includePath;
	}

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 */
	public function setNamespaceSeparator($sep) {
		$this->_namespaceSeparator = $sep;
	}

	/**
	 * Gets the namespace seperator used by classes in the namespace of this class loader.
	 *
	 * @return void
	 */
	public function getNamespaceSeparator() {
		return $this->_namespaceSeparator;
	}

	/**
	 * Sets the base include path for all class files in the namespace of this class loader.
	 *
	 * @param string $includePath
	 */
	public function setIncludePath($includePath) {
		$this->_includePath = $includePath;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 *
	 * @return string $includePath
	 */
	public function getIncludePath() {
		return $this->_includePath;
	}

	/**
	 * Sets the file extension of class files in the namespace of this class loader.
	 *
	 * @param string $fileExtension
	 */
	public function setFileExtension($fileExtension) {
		$this->_fileExtension = $fileExtension;
	}

	/**
	 * Gets the file extension of class files in the namespace of this class loader.
	 *
	 * @return string $fileExtension
	 */
	public function getFileExtension() {
		return $this->_fileExtension;
	}

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister() {
		spl_autoload_unregister(array($this, 'loadClass'));
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 * @return void
	 */
	public function loadClass($className) {
		$filename = $this->_includePath . '/' . implode('/', explode('\\', trim($className, '\\'))) . $this->_fileExtension;
		if (file_exists($filename) === FALSE && class_exists($className) === FALSE) {
			throw new \Exception('Class does not exist: ' . $className);
		} elseif (file_exists($filename)) {
			require_once $filename;
		}
	}

	/**
	 * @param string $interface
	 * @param string $actualClass
	 */
	public function registerImplementation($interface, $actualClass) {
		$this->_implementations[$interface] = $actualClass;
	}

	/**
	 * @param string $className
	 * @return object
	 */
	public function create($className) {
		$arguments = func_get_args();
		$className = array_shift($arguments);

		$this->loadClass($className);
		$classInterfaces = class_implements($className);
		if (in_array('TYPO3\\Fluid\\Object\\SingletonInterface', $classInterfaces)) {
			if (isset($this->_singletons[$className])) {
				return $this->_singletons[$className];
			}
		}

		$reflectedClass = new \ReflectionClass($className);
		$instance = $reflectedClass->newInstanceArgs($arguments);
		if (in_array('TYPO3\\Fluid\\Object\\SingletonInterface', $classInterfaces)) {
			$this->_singletons[$className] = $instance;
		}

		$classReflection = new \ReflectionClass($className);
		$methods = $classReflection->getMethods(\ReflectionMethod::IS_PUBLIC ^ \ReflectionMethod::IS_STATIC);
		foreach ($methods as $methodReflection) {
			$methodName = $methodReflection->getName();
			if (substr($methodName, 0, 6) === 'inject') {
				$arguments = $methodReflection->getParameters();
				$argumentName = $arguments[0]->getName();
				$docComment = $methodReflection->getDocComment();
				$lines = explode("\n", $docComment);
				$argumentType = NULL;
				foreach ($lines as $line) {
					if (strpos($line, '@param') && strpos($line, '$' . $argumentName)) {
						foreach (explode(' ', $line) as $segment) {
							if ($segment == '$' . $argumentName) {
								$argumentType = $last;
								break;
							}
							$last = $segment;
						}
					}
				}
				if ($argumentType) {
					if (isset($this->_implementations[$argumentType])) {
						$argumentType = $this->_implementations[$argumentType];
					}
					if (substr($argumentType, -9) === 'Interface') {
						$argumentType = substr($argumentType, 0, strlen($argumentType)-9);
					}
					$this->loadClass($argumentType);
					if (class_exists($className) === FALSE) {
						var_dump($className);
						exit();
					}
					$dependency = $this->create($argumentType);
					call_user_func_array(array($instance, $methodName), array($dependency));
				}
			}
		}


		return $instance;
	}

	/**
	 * @param string $className
	 * @return object
	 */
	public function get($className) {
		return $this->create($className);
	}

	/**
	 * @param string $className
	 * @return object
	 */
	public function getEmptyObject($className) {
		return $this->create($className);
	}

	/**
	 * Returns TRUE if an object with the given name is registered
	 *
	 * @param string $objectName Name of the object
	 * @return boolean TRUE if the object has been registered, otherwise FALSE
	 */
	public function isRegistered($objectName) {
		$this->loadClass($objectName);
		return class_exists($objectName);
	}

}