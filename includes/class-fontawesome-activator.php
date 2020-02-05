<?php
namespace FortAwesome;

require_once trailingslashit( dirname(__FILE__) ) . '../defines.php';
require_once trailingslashit( dirname(__FILE__) ) . 'class-fontawesome.php';

/**
 * Plugin activation logic.
 * 
 * The methods defined in this class should normally not be invoked directly,
 * but only by FontAwesome_Loader, which handles cases
 * where multiple installations of this plugin are present.
 * 
 * Most client code that depends upon this plugin should limit its invocations
 * to the corresponding Loader methods, such as FontAwesome_Loader::initialize().
 * 
 * @since 4.0.0
 */
class FontAwesome_Activator {

	/**
	 * Initializes plugin options only if they are empty.
	 *
	 * @since 4.0.0
	 * @throws FontAwesome_NoReleasesException
	 */
	public static function activate() {
		self::initialize();
	}

	/**
	 * Initializes plugin options with defaults only if they are empty.
	 *
	 * Otherwise, it leaves alone options that are already present.
	 * 
	 * Sets default user options. Will attempt to get the latest available version,
	 * which requires access to the Font Awesome API server. Throws FontAwesome_NoReleasesException
	 * when that request fails.
	 * 
	 * @param bool $force if true, overwrite any existing options with defaults
	 *
	 * @since 4.0.0
	 * @throws FontAwesome_NoReleasesException
	 */
	public static function initialize($force = FALSE) {
		if( $force || ! get_option( FontAwesome::OPTIONS_KEY ) ) {
			self::initialize_user_options();
		}

		if( $force || ! get_option( FontAwesome::CONFLICT_DETECTION_OPTIONS_KEY ) ) {
			self::initialize_conflict_detection_options();
		}
	}

	private static function initialize_user_options() {
		if ( 1 !== fa()->refresh_releases() ) {
			throw new FontAwesome_NoReleasesException("Sorry, we could not contact the Font Awesome API server.");
		}
		$version = fa()->latest_version();
		$options = array_merge( FontAwesome::DEFAULT_USER_OPTIONS, [ 'version' => $version ] );
		update_option( FontAwesome::OPTIONS_KEY, $options );
	}

	private static function initialize_conflict_detection_options() {
		update_option( FontAwesome::CONFLICT_DETECTION_OPTIONS_KEY, FontAwesome::DEFAULT_CONFLICT_DETECTION_OPTIONS );
	}
}

