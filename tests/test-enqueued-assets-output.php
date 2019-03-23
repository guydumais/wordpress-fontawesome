<?php
namespace FortAwesome;
/**
 * Class EnqueuedAssetsOutputTest
 *
 * Apparently, it's necessary to use the runTestsInSeparateProcesses annotation. Otherwise, the output buffering
 * seems to get confused between the tests, resulting in false negatives.
 * And since doing this normally involves serializing global data between parent and child processes, which doesn't
 * work in our case (singletons?), we also need to use preserverGlobalState disabled.
 *
 * @noinspection PhpCSValidationInspection
 *
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
// phpcs:ignoreFile Squiz.Commenting.ClassComment.Missing
// phpcs:ignoreFile Generic.Commenting.DocComment.MissingShort
require_once dirname( __FILE__ ) . '/_support/font-awesome-phpunit-util.php';

class EnqueuedAssetsOutputTest extends \WP_UnitTestCase {

	protected $mock_release_provider = null;

	/**
	 * Not using the other release provider mocking features because have multiple methods to mock at the same time.
	 * Probably means we're due for a refactor on the test utilities here so this doesn't have to be done ad-hoc.
	 */
	public function init_mock_release_provider() {
		$type = FontAwesome_Release_Provider::class;

		$mock_builder = $this->getMockBuilder( FontAwesome_Release_Provider::class )
		                    ->setMethods( [ 'releases', 'get_resource_collection' ] )
		                    ->disableOriginalConstructor();

		$mock = $mock_builder->getMock();

		try {
			$ref = new \ReflectionProperty( $type, 'instance' );
			$ref->setAccessible( true );
			$ref->setValue( null, $mock );
		} catch ( \ReflectionException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			error_log( 'Reflection error: ' . $e );
			return null;
		}

		$mock->method( 'releases' )->willReturn( get_mocked_releases() );

		$this->mock_release_provider = $mock;

		return $mock;
    }

	public function setUp() {
		FontAwesome::reset();
		$this->init_mock_release_provider();
		wp_script_is( 'font-awesome', 'enqueued' ) && wp_dequeue_script( 'font-awesome' );
		wp_script_is( 'font-awesome-v4shim', 'enqueued' ) && wp_dequeue_script( 'font-awesome-v4shim' );
		wp_style_is( 'font-awesome', 'enqueued' ) && wp_dequeue_style( 'font-awesome' );
		wp_style_is( 'font-awesome-v4shim', 'enqueued' ) && wp_dequeue_style( 'font-awesome-v4shim' );
	}

	/**
	 * @group output
	 */
	public function test_free_webfont_assets_enqueued() {
		$resource_collection = [
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/css/all.css',
				'sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ'
			),
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/css/v4-shims.css',
				'sha384-2QRS8Mv2zxkE2FAZ5/vfIJ7i0j+oF15LolHAhqFp9Tm4fQ2FEOzgPj4w/mWOTdnC'
			),
		];

		$this->mock_release_provider
			->method( 'get_resource_collection' )
			->willReturn( $resource_collection );

		global $fa_load;
		$fa_load->invoke( fa() );

		wp_head();

		# Make sure the main css looks right
		$this->expectOutputRegex('/<link[\s]+rel=\'stylesheet\'[\s]+id=\'font-awesome-official-css\'[\s]+href=\'https:\/\/use\.fontawesome\.com\/releases\/v5\.2\.0\/css\/all\.css\'[\s]+type=\'text\/css\'[\s]+media=\'all\'[\s]+integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK\/iSvJ\+a4\+0owXq79v\+lsFkW54bOGbiDQ"[\s]+crossorigin="anonymous"[\s]*\/>/');
		# Make sure the v4shim css looks right
		$this->expectOutputRegex('/<link[\s]+rel=\'stylesheet\'[\s]+id=\'font-awesome-official-v4shim-css\'[\s]+href=\'https:\/\/use\.fontawesome\.com\/releases\/v5\.2\.0\/css\/v4-shims\.css\'[\s]+type=\'text\/css\'[\s]+media=\'all\'[\s]+integrity="sha384-W14o25dsDf2S\/y9FS68rJKUyCoBGkLwr8owWTSTTHj4LOoHdrgSxw1cmNQMULiRb"[\s]+crossorigin="anonymous"[\s]*\/>/');
		# Make sure that the order is right: main css, followed by v4shim css
		$this->expectOutputRegex('/<link.+?font-awesome-official-css.+?>.+?<link.+?font-awesome-official-v4shim-css/s');
	}

	/**
	 * @group output
	 */
	public function test_free_svg_assets_enqueued() {
		$resource_collection = [
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/js/all.js',
				get_mocked_releases()['5.2.0']['sri']['free']['js/all.js']
			),
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/js/v4-shims.js',
				get_mocked_releases()['5.2.0']['sri']['free']['js/v4-shims.js']
			),
		];

		$this->mock_release_provider
			->method( 'get_resource_collection' )
			->willReturn( $resource_collection );

		add_action(
			'font_awesome_requirements',
			function() {
				fa()->register(
					array(
						'name' => 'test',
						'clientVersion' => '1',
						'method' => 'svg',
						'v4shim' => 'require',
					)
				);
			}
		);

		global $fa_load;
		$fa_load->invoke( fa() );

		wp_head();

		# Make sure the main <script> looks right
		$this->expectOutputRegex('/<script[\s]+defer[\s]+crossorigin="anonymous"[\s]+integrity="sha384-4oV5EgaV02iISL2ban6c\/RmotsABqE4yZxZLcYMAdG7FAPsyHYAPpywE9PJo\+Khy"[\s]+type=\'text\/javascript\'[\s]+src=\'https:\/\/use\.fontawesome\.com\/releases\/v5\.2\.0\/js\/all\.js\'><\/script>/');

		# Make sure the v4shim <script> looks right
		$this->expectOutputRegex('/<script[\s]+defer[\s]+crossorigin="anonymous"[\s]+integrity="sha384-rn4uxZDX7xwNq5bkqSbpSQ3s4tK9evZrXAO1Gv9WTZK4p1\+NFsJvOQmkos19ebn2"[\s]+type=\'text\/javascript\'[\s]+src=\'https:\/\/use\.fontawesome\.com\/releases\/v5\.2\.0\/js\/v4-shims\.js\'><\/script>/');

		# Make sure that the order is right: main script, followed by v4shim script
		$this->expectOutputRegex('/<script.+?all\.js.+?<script.+?v4-shims\.js/s');
	}

	/**
	 * @group pro
	 * @group output
	 */
	public function test_pro_webfont_assets_enqueued() {
		$resource_collection = [
			new FontAwesome_Resource(
				'https://pro.fontawesome.com/releases/v5.2.0/css/all.css',
				'sha384-TXfwrfuHVznxCssTxWoPZjhcss/hp38gEOH8UPZG/JcXonvBQ6SlsIF49wUzsGno'
			),
			new FontAwesome_Resource(
				'https://pro.fontawesome.com/releases/v5.2.0/css/v4-shims.css',
				'sha384-2QRS8Mv2zxkE2FAZ5/vfIJ7i0j+oF15LolHAhqFp9Tm4fQ2FEOzgPj4w/mWOTdnC'
			),
		];

		$this->mock_release_provider
			->method( 'get_resource_collection' )
			->willReturn( $resource_collection );

		global $fa_load;
		$fa_load->invoke( fa() );

		wp_head();

		# Make sure the main css looks right
		$this->expectOutputRegex('/<link[\s]+rel=\'stylesheet\'[\s]+id=\'font-awesome-official-css\'[\s]+href=\'https:\/\/pro\.fontawesome\.com\/releases\/v5\.2\.0\/css\/all\.css\'[\s]+type=\'text\/css\'[\s]+media=\'all\'[\s]+integrity="sha384-TXfwrfuHVznxCssTxWoPZjhcss\/hp38gEOH8UPZG\/JcXonvBQ6SlsIF49wUzsGno"[\s]+crossorigin="anonymous"[\s]*\/>/');
		# Make sure the v4shim css looks right
		$this->expectOutputRegex('/<link[\s]+rel=\'stylesheet\'[\s]+id=\'font-awesome-official-v4shim-css\'[\s]+href=\'https:\/\/pro\.fontawesome\.com\/releases\/v5\.2\.0\/css\/v4-shims\.css\'[\s]+type=\'text\/css\'[\s]+media=\'all\'[\s]+integrity="sha384-2QRS8Mv2zxkE2FAZ5\/vfIJ7i0j\+oF15LolHAhqFp9Tm4fQ2FEOzgPj4w\/mWOTdnC"[\s]+crossorigin="anonymous"[\s]*\/>/');
		# Make sure that the order is right: main css, followed by v4shim css
		$this->expectOutputRegex('/<link.+?font-awesome-official-css.+?>.+?<link.+?font-awesome-official-v4shim-css/s');
	}

	/**
	 * @group output
	 */
	public function test_pseudo_element_config_enqueued_when_svg() {
		$resource_collection = [
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/js/all.js',
				get_mocked_releases()['5.2.0']['sri']['free']['js/all.js']
			),
			new FontAwesome_Resource(
				'https://use.fontawesome.com/releases/v5.2.0/js/v4-shims.js',
				get_mocked_releases()['5.2.0']['sri']['free']['js/v4-shims.js']
			),
		];

		$this->mock_release_provider
			->method( 'get_resource_collection' )
			->willReturn( $resource_collection );

		add_action(
			'font_awesome_requirements',
			function() {
				fa()->register(
					array(
						'name' => 'test',
						'clientVersion' => '1',
						'method' => 'svg',
						'pseudoElements' => 'require',
						'v4shim' => 'require',
					)
				);
			}
		);

		global $fa_load;
		$fa_load->invoke( fa() );

		$this->assertTrue( fa()->using_pseudo_elements() );
		$this->assertEquals( 'svg', fa()->fa_method() );

		wp_head();

		$this->expectOutputRegex('/searchPseudoElements:\s*true/');
	}

	/**
	 * @group pro
	 */
	public function test_pro_svg_assets_enqueued() {
		$resource_collection = [
			new FontAwesome_Resource(
				'https://pro.fontawesome.com/releases/v5.2.0/js/all.js',
				'sha384-4oV5EgaV02iISL2ban6c/RmotsABqE4yZxZLcYMAdG7FAPsyHYAPpywE9PJo+Khy'
			),
			new FontAwesome_Resource(
				'https://pro.fontawesome.com/releases/v5.2.0/js/v4-shims.js',
				'sha384-aoMjEUBUPf5GpXx1WJUeTZ/gBmGqQB1u8uUc2J5LW2xnQtJKkGulESZ+rkoj182s'
			),
		];

		$this->mock_release_provider
			->method( 'get_resource_collection' )
			->willReturn( $resource_collection );

		add_action(
			'font_awesome_requirements',
			function() {
				fa()->register(
					array(
						'name' => 'test',
						'clientVersion' => '1',
						'method' => 'svg',
						'v4shim' => 'require',
					)
				);
			}
		);

		global $fa_load;
		$fa_load->invoke( fa() );

		wp_head();

		# Make sure the main <script> looks right
		$this->expectOutputRegex('/<script[\s]+defer[\s]+crossorigin="anonymous"[\s]+integrity="sha384-yBZ34R8uZDBb7pIwm\+whKmsCiRDZXCW1vPPn\/3Gz0xm4E95frfRNrOmAUfGbSGqN"[\s]+type=\'text\/javascript\'[\s]+src=\'https:\/\/pro\.fontawesome\.com\/releases\/v5\.2\.0\/js\/all\.js\'><\/script>/');

		# Make sure the v4shim <script> looks right
		$this->expectOutputRegex('/<script[\s]+defer[\s]+crossorigin="anonymous"[\s]+integrity="sha384-aoMjEUBUPf5GpXx1WJUeTZ\/gBmGqQB1u8uUc2J5LW2xnQtJKkGulESZ\+rkoj182s"[\s]+type=\'text\/javascript\'[\s]+src=\'https:\/\/pro\.fontawesome\.com\/releases\/v5\.2\.0\/js\/v4-shims\.js\'><\/script>/');

		# Make sure that the order is right: main script, followed by v4shim script
		$this->expectOutputRegex('/<script.+?all\.js.+?<script.+?v4-shims\.js/s');
	}
}
