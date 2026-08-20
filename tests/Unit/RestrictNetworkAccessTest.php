<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\RestrictNetworkAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestrictNetworkAccessTest extends TestCase
{
    public function test_allows_localhost_by_default()
    {
        $middleware = new RestrictNetworkAccess();
        $request = Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $nextCalled = false;
        $next = function ($req) use (&$nextCalled) {
            $nextCalled = true;
            return response('OK');
        };

        Config::set('app.allowed_ips', '192.168.1.100'); // Block IP

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_allows_exact_ip_match()
    {
        $middleware = new RestrictNetworkAccess();
        $request = Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', '10.120.29.5');

        $nextCalled = false;
        $next = function ($req) use (&$nextCalled) {
            $nextCalled = true;
            return response('OK');
        };

        Config::set('app.allowed_ips', '10.120.29.5,192.168.1.1');

        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_allows_ip_in_hyphenated_range()
    {
        $middleware = new RestrictNetworkAccess();
        $next = function ($req) {
            return response('OK');
        };

        Config::set('app.allowed_ips', '10.120.29.1-10.120.29.200');

        // Test inside range (lower bound)
        $req1 = Request::create('/', 'GET');
        $req1->server->set('REMOTE_ADDR', '10.120.29.1');
        $this->assertEquals('OK', $middleware->handle($req1, $next)->getContent());

        // Test inside range (middle)
        $req2 = Request::create('/', 'GET');
        $req2->server->set('REMOTE_ADDR', '10.120.29.100');
        $this->assertEquals('OK', $middleware->handle($req2, $next)->getContent());

        // Test inside range (upper bound)
        $req3 = Request::create('/', 'GET');
        $req3->server->set('REMOTE_ADDR', '10.120.29.200');
        $this->assertEquals('OK', $middleware->handle($req3, $next)->getContent());

        // Test outside range (below)
        $req4 = Request::create('/', 'GET');
        $req4->server->set('REMOTE_ADDR', '10.120.29.0');
        
        $this->expectException(HttpException::class);
        $middleware->handle($req4, $next);
    }

    public function test_blocks_ip_outside_hyphenated_range()
    {
        $middleware = new RestrictNetworkAccess();
        $next = function ($req) {
            return response('OK');
        };

        Config::set('app.allowed_ips', '10.120.29.1-10.120.29.200');

        $req = Request::create('/', 'GET');
        $req->server->set('REMOTE_ADDR', '10.120.29.201');

        $this->expectException(HttpException::class);
        $middleware->handle($req, $next);
    }

    public function test_allows_ip_in_short_range_notation()
    {
        $middleware = new RestrictNetworkAccess();
        $next = function ($req) {
            return response('OK');
        };

        Config::set('app.allowed_ips', '10.120.29.5-20');

        // Test inside short range
        $req1 = Request::create('/', 'GET');
        $req1->server->set('REMOTE_ADDR', '10.120.29.12');
        $this->assertEquals('OK', $middleware->handle($req1, $next)->getContent());

        // Test outside short range
        $req2 = Request::create('/', 'GET');
        $req2->server->set('REMOTE_ADDR', '10.120.29.21');

        $this->expectException(HttpException::class);
        $middleware->handle($req2, $next);
    }

    public function test_allows_ip_in_cidr_subnet()
    {
        $middleware = new RestrictNetworkAccess();
        $next = function ($req) {
            return response('OK');
        };

        Config::set('app.allowed_ips', '10.120.29.0/24');

        $req1 = Request::create('/', 'GET');
        $req1->server->set('REMOTE_ADDR', '10.120.29.155');
        $this->assertEquals('OK', $middleware->handle($req1, $next)->getContent());

        $req2 = Request::create('/', 'GET');
        $req2->server->set('REMOTE_ADDR', '10.120.30.1');

        $this->expectException(HttpException::class);
        $middleware->handle($req2, $next);
    }

    public function test_blocks_explicitly_blocked_ip()
    {
        $middleware = new RestrictNetworkAccess();
        $next = function ($req) {
            return response('OK');
        };

        Config::set('allowed_ips.allowed', ['10.120.29.1-10.120.29.200']);
        Config::set('allowed_ips.blocked', ['10.120.29.50']);

        // Allowed IP
        $req1 = Request::create('/', 'GET');
        $req1->server->set('REMOTE_ADDR', '10.120.29.10');
        $this->assertEquals('OK', $middleware->handle($req1, $next)->getContent());

        // Blocked IP within the range
        $req2 = Request::create('/', 'GET');
        $req2->server->set('REMOTE_ADDR', '10.120.29.50');

        $this->expectException(HttpException::class);
        $middleware->handle($req2, $next);
    }
}
