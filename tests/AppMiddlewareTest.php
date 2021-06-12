<?php

namespace FrameworkX\Tests;

use FastRoute\RouteCollector;
use FrameworkX\App;
use FrameworkX\MiddlewareHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\ServerRequest;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class AppMiddlewareTest extends TestCase
{
    public function testGetMethodWithMiddlewareAddsGetRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['GET'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->get('/', $middleware, $controller);
    }

    public function testHeadMethodWithMiddlewareAddsHeadRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['HEAD'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->head('/', $middleware, $controller);
    }

    public function testPostMethodWithMiddlewareAddsPostRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['POST'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->post('/', $middleware, $controller);
    }

    public function testPutMethodWithMiddlewareAddsPutRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['PUT'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->put('/', $middleware, $controller);
    }

    public function testPatchMethodWithMiddlewareAddsPatchRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['PATCH'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->patch('/', $middleware, $controller);
    }

    public function testDeleteMethodWithMiddlewareAddsDeleteRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['DELETE'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->delete('/', $middleware, $controller);
    }

    public function testOptionsMethodWithMiddlewareAddsOptionsRouteOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['OPTIONS'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->options('/', $middleware, $controller);
    }

    public function testAnyMethodWithMiddlewareAddsAllHttpMethodsOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->any('/', $middleware, $controller);
    }

    public function testMapMethodWithMiddlewareAddsGivenMethodsOnRouter()
    {
        $app = new App();

        $middleware = function () {};
        $controller = function () { };

        $router = $this->createMock(RouteCollector::class);
        $router->expects($this->once())->method('addRoute')->with(['GET', 'POST'], '/', new MiddlewareHandler([$middleware, $controller]));

        $ref = new \ReflectionProperty($app, 'router');
        $ref->setAccessible(true);
        $ref->setValue($app, $router);

        $app->map(['GET', 'POST'], '/', $middleware, $controller);
    }

    public function testMiddlewareCallsNextReturnsResponseFromRouter()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            return $next($request);
        };

        $handler = function () {
            return new Response(
                200,
                [
                    'Content-Type' => 'text/html'
                ],
                "OK\n"
            );
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $response = $ref->invoke($app, $request);

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals("OK\n", (string) $response->getBody());
    }

    public function testMiddlewareCallsNextWithModifiedRequestReturnsResponseFromRouter()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            return $next($request->withAttribute('name', 'Alice'));
        };

        $handler = function (ServerRequestInterface $request) {
            return new Response(
                200,
                [
                    'Content-Type' => 'text/html'
                ],
                $request->getAttribute('name')
            );
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $response = $ref->invoke($app, $request);

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('Alice', (string) $response->getBody());
    }

    public function testMiddlewareCallsNextReturnsResponseModifiedInMiddlewareFromRouter()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            $response = $next($request);
            assert($response instanceof ResponseInterface);

            return $response->withHeader('Content-Type', 'text/html');
        };

        $handler = function (ServerRequestInterface $request) {
            return new Response(
                200,
                [],
                'Alice'
            );
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $response = $ref->invoke($app, $request);

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('Alice', (string) $response->getBody());
    }

    public function testMiddlewareCallsNextReturnsDeferredResponseModifiedInMiddlewareFromRouter()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            $promise = $next($request);
            assert($promise instanceof PromiseInterface);

            return $promise->then(function (ResponseInterface $response) {
                return $response->withHeader('Content-Type', 'text/html');
            });
        };

        $handler = function (ServerRequestInterface $request) {
            return resolve(new Response(
                200,
                [],
                'Alice'
            ));
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $promise = $ref->invoke($app, $request);

        /** @var PromiseInterface $promise */
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = null;
        $promise->then(function ($value) use (&$response) {
            $response = $value;
        });

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('Alice', (string) $response->getBody());
    }

    public function testMiddlewareCallsNextReturnsCoroutineResponseModifiedInMiddlewareFromRouter()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            $generator = $next($request);
            assert($generator instanceof \Generator);

            $response = yield from $generator;
            assert($response instanceof ResponseInterface);

            return $response->withHeader('Content-Type', 'text/html');
        };

        $handler = function (ServerRequestInterface $request) {
            $name = yield resolve('Alice');
            return new Response(
                200,
                [],
                $name
            );
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $promise = $ref->invoke($app, $request);

        /** @var PromiseInterface $promise */
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = null;
        $promise->then(function ($value) use (&$response) {
            $response = $value;
        });

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('Alice', (string) $response->getBody());
    }

    public function testMiddlewareCallsNextWhichThrowsExceptionReturnsInternalServerErrorResponse()
    {
        $app = new App();

        $middleware = function (ServerRequestInterface $request, callable $next) {
            return $next($request);
        };

        $line = __LINE__ + 2;
        $handler = function () {
            throw new \RuntimeException('Foo');
        };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $response = $ref->invoke($app, $request);

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals("500 (Internal Server Error): Expected request handler to return <code>Psr\Http\Message\ResponseInterface</code> but got uncaught <code>RuntimeException</code> (<code title=\"See " . __FILE__ . " line $line\">AppMiddlewareTest.php:$line</code>): Foo\n", (string) $response->getBody());
    }

    public function testMiddlewareWhichThrowsExceptionReturnsInternalServerErrorResponse()
    {
        $app = new App();

        $line = __LINE__ + 2;
        $middleware = function (ServerRequestInterface $request, callable $next) {
            throw new \RuntimeException('Foo');
        };

        $handler = function () { };

        $app->get('/', $middleware, $handler);

        $request = new ServerRequest('GET', 'http://localhost/');

        // $response = $app->handleRequest($request);
        $ref = new \ReflectionMethod($app, 'handleRequest');
        $ref->setAccessible(true);
        $response = $ref->invoke($app, $request);

        /** @var ResponseInterface $response */
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals("500 (Internal Server Error): Expected request handler to return <code>Psr\Http\Message\ResponseInterface</code> but got uncaught <code>RuntimeException</code> (<code title=\"See " . __FILE__ . " line $line\">AppMiddlewareTest.php:$line</code>): Foo\n", (string) $response->getBody());
    }
}
