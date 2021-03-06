<?php
namespace FortAwesome;

require_once dirname( __FILE__ ) . '/../includes/error-util.php';

use function FortAwesome\unknown_error_500;
use function FortAwesome\fa_400;
use function FortAwesome\fa_500;
use \Exception, \Error;

/**
 * Class ErrorUtilTest
 */
class ErrorUtilTest extends \WP_UnitTestCase {
	public function test_wpe_fontawesome_unknown_error_with_exception() {
		$message = 'foo';
		$code    = 'fontawesome_unknown_error';
		$e       = new Exception( $message );
		$result  = wpe_fontawesome_unknown_error( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertEquals( $message, $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_wpe_fontawesome_unknown_error_with_error() {
		$message = 'foo';
		$code    = 'fontawesome_unknown_error';
		$e       = new Error( $message );
		$result  = wpe_fontawesome_unknown_error( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertEquals( $message, $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_wpe_fontawesome_unknown_error_with_array() {
		$message = 'foo';
		$code    = 'fontawesome_unknown_error';
		$e       = array( 'alpha' => 42 );
		$result  = wpe_fontawesome_unknown_error( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertStringEndsWith( 'cannot be stringified.', $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_wpe_fontawesome_unknown_error_with_string() {
		$message = 'foo';
		$code    = 'fontawesome_unknown_error';
		$e       = $message;
		$result  = wpe_fontawesome_unknown_error( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertStringStartsWith( 'Unexpected Thing', $result->get_error_message( $code ) );
		$this->assertStringEndsWith( 'foo', $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_fontawesome_client_exception_with_exception() {
		$message = 'foo';
		$code    = 'fontawesome_client_exception';
		$e       = new Exception( $message );
		$result  = wpe_fontawesome_client_exception( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertEquals( $message, $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_fontawesome_client_exception_with_error() {
		$message = 'foo';
		$code    = 'fontawesome_client_exception';
		$e       = new Error( $message );
		$result  = wpe_fontawesome_client_exception( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertEquals( $message, $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_fontawesome_server_exception_with_exception() {
		$message = 'foo';
		$code    = 'fontawesome_server_exception';
		$e       = new Exception( $message );
		$result  = wpe_fontawesome_server_exception( $e );

		$this->assertTrue( is_a( $result, 'WP_Error' ) );
		$this->assertEquals( $code, $result->get_error_code() );
		$this->assertEquals( $message, $result->get_error_message( $code ) );
		$this->assertTrue( isset( $result->get_error_data( $code )['trace'] ) );
	}

	public function test_build_wp_error_with_a_previous() {
		$prev = new Exception( 'some previous' );
		$e    = PreferenceRegistrationException::with_thrown( $prev );

		$result = wpe_fontawesome_server_exception( $e );

		$this->assertEquals( [ 'fontawesome_server_exception', 'previous_exception' ], $result->get_error_codes() );
	}
}
