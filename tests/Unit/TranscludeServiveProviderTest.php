<?php

namespace CHStudio\LaravelTransclude\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Factory;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
use CHStudio\LaravelTransclude\TranscludeCompiler;
use CHStudio\LaravelTransclude\TranscludeServiceProvider as SUT;

class TranscludeServiceProviderTest extends TestCase
{
    public function testBladeDirectivesAreRegistered()
    {
        $blade = $this->createMock(BladeCompiler::class);
        $blade //Ensure Blade directives are called
            ->expects($this->exactly(3))
            ->method('directive')
            ->withConsecutive(
                [$this->equalTo('transclude'), $this->callback(function($item) {
                    $this->assertTrue(is_callable($item));
                    $this->assertInstanceOf(TranscludeCompiler::class, $item[0]);
                    $this->assertEquals('compileTransclude', $item[1]);

                    return true;
                })],
                [$this->equalTo('endtransclude'), $this->callback(function($item) {
                    $this->assertTrue(is_callable($item));
                    $this->assertInstanceOf(TranscludeCompiler::class, $item[0]);
                    $this->assertEquals('compileEndTransclude', $item[1]);

                    return true;
                })],
                [$this->equalTo('transcluded'), $this->callback(function($item) {
                    $this->assertTrue(is_callable($item));
                    $this->assertInstanceOf(TranscludeCompiler::class, $item[0]);
                    $this->assertEquals('compileTranscluded', $item[1]);

                    return true;
                })]
            );

        $engine = $this->createMock(CompilerEngine::class);
        $engine
            ->expects($this->exactly(3))
            ->method('getCompiler')
            ->will($this->returnValue($blade));

        $resolver = new EngineResolver;
        $resolver->register('blade', function() use ($engine) {
            return $engine;
        });

        $factory = $this->createMock(Factory::class);
        $factory
            ->expects($this->exactly(3))
            ->method('getEngineResolver')
            ->will($this->returnValue($resolver));

        $factory //Check view environement is shared
            ->expects($this->once())
            ->method('share')
            ->with(
                $this->callback(function($item) {
                    $this->assertEquals((new TranscludeCompiler)->getName(), $item);
                    return true;
                }),
                $this->callback(function($item) {
                    $this->assertInstanceOf(TranscludeCompiler::class, $item);
                    return true;
                })
            );

        $app = $this->createMock(\ArrayAccess::class);
        $app
            ->expects($this->any())
            ->method('offsetGet')
            ->with('view')
            ->will($this->returnValue($factory));

        Facade::setFacadeApplication($app);
        $sut = new SUT($app);

        $sut->boot();
    }
}
