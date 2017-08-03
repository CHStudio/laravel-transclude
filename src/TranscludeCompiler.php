<?php

namespace CHStudio\LaravelTransclude;

use CHStudio\LaravelTransclude\Exceptions\TranscludeNotStarted;
use CHStudio\LaravelTransclude\Exceptions\MissingTranscludeDirective;
use Illuminate\Support\Str;

class TranscludeCompiler
{
    /**
     * Environment variable name
     * @var string
     */
    private $name;

    /**
     * Stack of transclude views
     * @var array
     */
    private $transcludeStack = [];

    /**
     * Transcluded compiled content
     * @var array
     */
    private $transcludedContent = [];

    /**
     * Initialize compiler
     * @param string $name
     */
    public function __construct($name = '__transcluder')
    {
        $this->name = $name;
    }

    /**
     * Retrieve environment variable name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Compile the transclude statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    public function compileTransclude($expression)
    {
        $this->transcludeStack[] = $this->stripParentheses($expression);

        return "<?php \${$this->name}->startTranscluding(); ?>";
    }

    /**
     * Compile the entransclude statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    public function compileEndTransclude()
    {
        if (empty($this->transcludeStack)) {
            throw new TranscludeNotStarted(
                'Cannot end a transclude without first starting one.'
            );
        }

        // get the view name and parameters to transclude into
        $expression = array_pop($this->transcludeStack);
        $compiled = <<<PHP
<?php
    \${$this->name}->endTranscluding();
    echo \$__env
        ->make($expression, array_except(get_defined_vars(), array('__data', '__path')))
        ->render();
?>
PHP;
        return $compiled;
    }

    /**
     * Compile the transcluded statements into valid PHP.
     *
     * @return string
     */
    public function compileTranscluded()
    {
        return "<?php echo \${$this->name}->echoLatestTranscluded(); ?>";
    }

    /**
     * Start buffering
     */
    public function startTranscluding()
    {
        ob_start();
    }

    /**
     * End buffering and save content
     */
    public function endTranscluding()
    {
        $this->transcludedContent[] = ob_get_clean();
    }

    /**
     * Return latest buffered content and drop it from collection
     *
     * @return string
     */
    public function echoLatestTranscluded()
    {
        if (0 === count($this->transcludedContent)) {
            throw new MissingTranscludeDirective(
                'Cannot use transcluded directive without opening a transclude one.'
            );
        }

        return array_pop($this->transcludedContent);
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string  $expression
     * @return string
     */
    protected function stripParentheses($expression)
    {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }
}
