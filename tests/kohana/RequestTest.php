<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for request class
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_RequestTest extends Unittest_TestCase
{
	/**
	 * Provides the data for test_create()
	 * @return  array
	 */
	public function provider_create()
	{
		return array(
			array('foo/bar', 'Request_Client_Internal'),
			array('http://google.com', 'Request_Client_External'),
		);
	}
	
	/**
	 * Ensures the create class is created with the correct client
	 *
	 * @test
	 * @dataProvider provider_create
	 */
	public function test_create($uri, $client_class)
	{
		$request = Request::factory($uri);
		$client = $request->get_client();

		$this->assertEquals(get_class($client), $client_class);
	}

	/**
	 * Ensure that parameters can be read
	 *
	 * @test
	 */
	public function test_param()
	{
		$uri = 'foo/bar';
		$request = Request::factory($uri);

		$this->assertEquals($request->param('uri'), $uri);
		$this->assertEquals(is_array($request->param()), TRUE);
	}

	/**
	 * Provides data for Request::create_response()
	 */
	public function provider_create_response()
	{
		return array(
			array('foo/bar', TRUE, TRUE),
			array('foo/bar', FALSE, FALSE)
		);
	}

	/**
	 * Ensures a request creates an empty response, and binds correctly
	 * 
	 * @test
	 * @dataProvider  provider_create_response
	 */
	public function test_create_response($uri, $bind, $equality)
	{
		$request = Request::factory($uri);
		$response = $request->create_response($bind);

		$this->assertEquals(($request->response() === $response), $equality);
	}

	/**
	 * Tests Request::response()
	 * 
	 * @test
	 */
	public function test_response()
	{
		$request = Request::factory('foo/bar');
		$response = $request->create_response(FALSE);

		$this->assertEquals($request->response(), NULL);
		$this->assertEquals(($request->response($response) === $request), TRUE);
		$this->assertEquals(($request->response() === $response), TRUE);
	}

	/**
	 * Tests Request::method()
	 * 
	 * @test
	 */
	public function test_method()
	{
		$request = Request::factory('foo/bar');

		$this->assertEquals($request->method(), 'GET');
		$this->assertEquals(($request->method('post') === $request), TRUE);
		$this->assertEquals(($request->method() === 'POST'), TRUE);
	}

	/**
	 * Tests Request::accept_type()
	 *
	 * @test
	 * @covers Request::accept_type
	 */
	public function test_accept_type()
	{
		$this->assertEquals(array('*/*' => 1), Request::accept_type());
	}

	/**
	 * Provides test data for Request::accept_lang()
	 * @return array
	 */
	public function provider_accept_lang()
	{
		return array(
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5'))),
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'en-gb'))),
			array('en-us', 1, array('_SERVER' => array('HTTP_ACCEPT_LANGUAGE' => 'sp-sp;q=0.5')))
		);
	}

	/**
	 * Tests Request::accept_lang()
	 *
	 * @test
	 * @covers Request::accept_lang
	 * @dataProvider provider_accept_lang
	 * @param array $params Query string
	 * @param string $expected Expected result
	 * @param array $enviroment Set environment
	 */
	public function test_accept_lang($params, $expected, $enviroment)
	{
		$this->setEnvironment($enviroment);

		$this->assertEquals(
			$expected,
			Request::accept_lang($params)
		);
	}

	/**
	 * Provides test data for Request::url()
	 * @return array
	 */
	public function provider_url()
	{
		return array(
			array(
				'foo/bar',
				array(),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
			array(
				'foo',
				array('action' => 'bar'),
				'http',
				TRUE,
				'http://localhost/kohana/foo/bar'
			),
		);
	}

	/**
	 * Tests Request::url()
	 *
	 * @test
	 * @dataProvider provider_url
	 * @covers Request::url
	 * @param string $route the route to use
	 * @param array $params params to pass to route::uri
	 * @param string $protocol the protocol to use
	 * @param array $expected The string we expect
	 */
	public function test_url($uri, $params, $protocol, $is_cli, $expected)
	{
		$this->setEnvironment(array(
			'Kohana::$base_url'  => '/kohana/',
			'_SERVER'            => array('HTTP_HOST' => 'localhost', 'argc' => $_SERVER['argc']),
			'Kohana::$index_file' => FALSE,
			'Kohana::$is_cli'    => $is_cli,
		));

		$this->assertEquals(Request::factory($uri)->url($params, $protocol), $expected);
	}
}

class Controller_Foo extends Controller {
	public function action_bar()
	{
		$this->response->body('foo');
	}
}
