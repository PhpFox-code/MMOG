<?php
/**
 * Dispatches requests to a controller and an action. Can be called by the
 * controller live to forward to another controller.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Core_Router
{
	/**
	 * Router constructor, try and load a controller and action.
	 *
	 * @access public
	 */
	public function __construct() {
		// Get the address the user navigated to
		Core_Url::getUrlBreakdown();

		// Inform the bootstrap a request has been initialised
		Core_Bootstrap::initRequest($_GET['controller'], $_GET['action']);

		// Try and instantiate the controller
		$this->loadController($_GET['controller']);
	}

	/**
	 * Try and load the controller.
	 *
	 * @access public
	 * @param  string $controller
	 * @param  string $action
     * @static
	 */
	public static function loadController($controller, $action = '') {
		// Format the controller name correctly
		$controller = 'Controller_' . $controller;

		// Can we load the controller?
		try {
			// Instantiate
			$controller = new $controller();

			// We need to set the child to the parent so we can forward
			$controller->child = $controller;

			// Inform the bootstrap a controller has been initialised
			Core_Bootstrap::initController($controller);

			// Call the init method, if it exists
			if (method_exists($controller, 'init')) {
				$controller->init();
			}

			// And set the Core_View for View Helpers
			Core_ViewHelper::setView($controller->view);
		} catch (Exception $e) {
			// Forward to the utilities 404
			die('Sorry, we were unable to load the page your requested.');
		}

		// Which action shall we run?
		$action = $action ? $action : $_GET['action'];

		// Load the action
		Core_Router::loadAction($controller, $action);
	}

	/**
	 * Try and run the action.
	 *
	 * @access public
	 * @param  string $controller
	 * @param  string $action
     * @return boolean
     * @static
	 */
	public static function loadAction($controller, $action) {
		// We want pretty URL's, there might be dashes
		$action = str_replace('-', '', $action);

		// Does the method exist?
		$actionExists = method_exists($controller, $action . 'Action');

		// If the method does not exist then we need to run the error action
		if (! $actionExists && $action != 'error') {
			// There was an error with the action, and we were not running the 404 action
			// Try and run the 404 action
			Core_Router::loadAction($controller, 'error');

			// No need to go any further
			return false;
		}

		// Yes, it exists
		// Set the controller and action that we are heading to
		$controller->view->controller = str_replace('Controller_', '', get_class($controller));
		$controller->view->action     = $action;

		// Start the cache, if required
		$controller->cache();

		// And call the action
		if ($actionExists) {
			$controller->{$action . 'Action'}();
		}

		// And now render the view
		$controller->render();
	}
}