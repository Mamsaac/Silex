<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Error handler test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class BeforeAfterFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeAndAfterFilter()
    {
        $i = 0;
        $test = $this;

        $app = new Application();

        $app->before(function() use (&$i, $test) {
            $test->assertEquals(0, $i);
            $i++;
        });

        $app->match('/foo', function() use (&$i, $test) {
            $test->assertEquals(1, $i);
            $i++;
        });

        $app->after(function() use (&$i, $test) {
            $test->assertEquals(2, $i);
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(3, $i);
    }

    public function testAfterFilterWithResponseObject()
    {
        $i = 0;

        $app = new Application();

        $app->match('/foo', function() use (&$i) {
            $i++;
            return new Response('foo');
        });

        $app->after(function() use (&$i) {
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }

    public function testMultipleFilters()
    {
        $i = 0;
        $test = $this;

        $app = new Application();

        $app->before(function() use (&$i, $test) {
            $test->assertEquals(0, $i);
            $i++;
        });

        $app->before(function() use (&$i, $test) {
            $test->assertEquals(1, $i);
            $i++;
        });

        $app->match('/foo', function() use (&$i, $test) {
            $test->assertEquals(2, $i);
            $i++;
        });

        $app->after(function() use (&$i, $test) {
            $test->assertEquals(3, $i);
            $i++;
        });

        $app->after(function() use (&$i, $test) {
            $test->assertEquals(4, $i);
            $i++;
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(5, $i);
    }

    public function testFiltersShouldFireOnException()
    {
        $i = 0;

        $app = new Application();

        $app->before(function() use (&$i) {
            $i++;
        });

        $app->match('/foo', function() {
            throw new \RuntimeException();
        });

        $app->after(function() use (&$i) {
            $i++;
        });

        $app->error(function() {
            return 'error handled';
        });

        $request = Request::create('/foo');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }

    public function testFiltersShouldFireOnHttpException()
    {
        $i = 0;

        $app = new Application();

        $app->before(function() use (&$i) {
            $i++;
        });

        $app->after(function() use (&$i) {
            $i++;
        });

        $app->error(function() {
            return 'error handled';
        });

        $request = Request::create('/nowhere');
        $app->handle($request);

        $this->assertEquals(2, $i);
    }
}
