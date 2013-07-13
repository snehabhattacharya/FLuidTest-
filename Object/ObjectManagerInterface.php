<?php
namespace TYPO3\Fluid\Object;

/**
 * Interface for the TYPO3 Object Manager
 *
 */
interface ObjectManagerInterface
{
	/**
	 * Returns TRUE if an object with the given name is registered
	 *
	 * @param string $objectName Name of the object
	 * @return boolean TRUE if the object has been registered, otherwise FALSE
	 */
	public function isRegistered($objectName);

	/**
	 * Returns a fresh or existing instance of the object specified by $objectName.
	 *
	 * Important:
	 *
	 * If possible, instances of Prototype objects should always be created with the
	 * Object Manager's create() method and Singleton objects should rather be
	 * injected by some type of Dependency Injection.
	 *
	 * @param string $objectName The name of the object to return an instance of
	 * @return object The object instance
	 * @api
	 */
	public function get($objectName);

	/**
	 * Creates a fresh instance of the object specified by $objectName.
	 *
	 * This factory method can only create objects of the scope prototype.
	 * Singleton objects must be either injected by some type of Dependency Injection or
	 * if that is not possible, be retrieved by the get() method of the
	 * Object Manager
	 *
	 * @param string $objectName The name of the object to create
	 * @return object The new object instance
	 * @api
	 */
	public function create($objectName);

	/**
	 * Create an instance of $className without calling its constructor
	 *
	 * @param string $className
	 * @return object
	 * @api
	 */
	public function getEmptyObject($className);

}
