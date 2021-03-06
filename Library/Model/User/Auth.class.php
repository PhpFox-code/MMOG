<?php
/**
 * Handles keeping a track of who is logged in and whether we need to reload their
 * data to ensure it is fresh.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       18/09/2012
 */
class Model_User_Auth
{
	/**
	 * Does the user have an identity?
	 * 
	 * @access public
	 * @return boolean
     * @static
	 */
	public static function hasIdentity() {
		return Core_Store::has('identity');
	}

	/**
	 * Set the identity of the user.
	 *
	 * To save having to fetch the users data on each reload when it rarely changes
	 * we will only get it once we seem it to be stale, in this case after 60 seconds.
	 * 
	 * @access public
	 * @param  Model_User_Instance $user
	 * @param  int                 $stale
	 * @throws Exception           If user is not a Model_User_Interface.
     * @static
	 */
	public static function putIdentity($user, $stale = 60) {
		// Have we been passed a Model_User_Instance?
		if (get_class($user) !== 'Model_User_Instance') {
			throw new Exception('Incorrect identity passed, expected a Model_User_Instance.');
		}

		// Set the identity, serialized, and also when this data becomes stale
		Core_Store::put(
			'identity',
			array(
				'instance' => $user,
				'stale'    => $_SERVER['REQUEST_TIME'] + $stale
			)
		);
	}

	/**
	 * Return the identity of the user logged in.
	 *
	 * We automatically refresh this data
	 * 
	 * @access public
	 * @return Model_User_Instance
	 * @throws Exception           If the user isn't currently logged in.
     * @static
	 */
	public static function getIdentity() {
		// Get the identity
		$user = Core_Store::get('identity');

		// Do we have an identity?
		if (! $user) {
			throw new Exception('No identity is currently stored.');
		}

		// Do we need to refresh the users information?
		if ($_SERVER['REQUEST_TIME'] > $user['stale']) {
			// Reload data
			// Create a new user model
			$user = new Model_User_Instance($user['instance']->getInfo('user_id'));

			// Save this identity
			Model_User_Auth::putIdentity($user);

			// And return it
			return $user;
		}

		// Return the identity
		return $user['instance'];
	}

	/**
	 * Remove the identity, logging the user out.
	 * 
	 * @access public
     * @static
	 */
	public static function removeIdentity() {
		Core_Store::remove('identity');
	}
}