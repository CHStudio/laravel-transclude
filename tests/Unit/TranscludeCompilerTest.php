<?php

namespace CHStudio\LaravelTransclude\Tests\Unit;

use PHPUnit\Framework\TestCase;
use CHStudio\LaravelTransclude\TranscludeCompiler as SUT;

class TranscludeCompilerTest extends TestCase
{
    public function testNameDefault()
    {
        $sut = new SUT;
        $this->assertEquals('__transcluder', $sut->getName());
    }

    public function testNameSet()
    {
        $sut = new SUT('aSuperName');
        $this->assertEquals('aSuperName', $sut->getName());
    }

    public function testTranscludeDirectiveOutput()
    {
        $sut = new SUT;
        $this->assertEquals(
            "<?php \$__transcluder->startTranscluding(); ?>",
            $sut->compileTransclude('view')
        );
    }

    public function giveExpressions()
    {
        return [
            ['view'],
            ["'view'"],
            ["'elements', ['heading' => 'title', 'value' => 'other']"]
        ];
    }

    /**
     * @dataProvider giveExpressions
     */
    public function testTranscludeDirectiveAddViewToTheStack($expression)
    {
        $sut = new SUT;
        $sut->compileTransclude($expression);
        $reflection = (new \ReflectionClass($sut))->getProperty('transcludeStack');
        $reflection->setAccessible(true);

        $this->assertEquals([$expression], $reflection->getValue($sut));
    }

    public function testTranscludeDirectiveWithParenthesisAddViewToTheStack()
    {
        $sut = new SUT;
        $sut->compileTransclude('(view)');
        $reflection = (new \ReflectionClass($sut))->getProperty('transcludeStack');
        $reflection->setAccessible(true);

        $this->assertEquals(['view'], $reflection->getValue($sut));
    }

    public function testTranscludedDirectiveOutput()
    {
        $sut = new SUT;
        $this->assertEquals(
            "<?php echo \$__transcluder->echoLatestTranscluded(); ?>",
            $sut->compileTranscluded()
        );
    }

    /**
     * @expectedException \CHStudio\LaravelTransclude\Exceptions\TranscludeNotStarted
     */
    public function testEndTranscludeDirectiveCantBeCalledBeforeStarting()
    {
        $sut = new SUT;
        $sut->compileEndTransclude();
    }

    /**
     * @dataProvider giveExpressions
     */
    public function testEndTranscludeDirectiveOutput($expression)
    {
        $sut = new SUT;
        $sut->compileTransclude($expression);
        $output = $sut->compileEndTransclude();

        $this->assertContains(
            "\$__transcluder->endTranscluding();",
            $output
        );
        $this->assertContains(
            "->make($expression, array_except(get_defined_vars(), array('__data', '__path')))",
            $output
        );
        $this->assertContains(
            "->render()",
            $output
        );
    }

    public function testStartTranscluding()
    {
        $level = ob_get_level();
        $sut = new SUT();
        $sut->startTranscluding();
        $this->assertEquals($level + 1, ob_get_level());
        $sut->endTranscluding();
        $this->assertEquals($level, ob_get_level());
    }

    public function testEndTranscluding()
    {
        $sut = new SUT();
        $sut->startTranscluding();
        echo "in buffer";
        $output = $sut->endTranscluding();

        $reflection = (new \ReflectionClass($sut))->getProperty('transcludedContent');
        $reflection->setAccessible(true);

        $this->assertEquals(['in buffer'], $reflection->getValue($sut));
    }

    /**
     * @expectedException \CHStudio\LaravelTransclude\Exceptions\MissingTranscludeDirective
     */
    public function testEchoLatestTranscludedWithoutTransclusion()
    {
        $sut = new SUT();

        $sut->echoLatestTranscluded();
    }

    public function testEchoLatestTranscluded()
    {
        $sut = new SUT();
        $sut->startTranscluding();
        echo "in buffer";
        $sut->endTranscluding();
        $sut->startTranscluding();
        echo "in buffer 2";
        $sut->endTranscluding();

        $this->assertEquals('in buffer 2', $sut->echoLatestTranscluded());
        $this->assertEquals('in buffer', $sut->echoLatestTranscluded());
    }
}
