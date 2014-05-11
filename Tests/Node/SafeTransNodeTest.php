<?php
namespace Arthens\SafeTranslation\Tests\Node;

use Arthens\SafeTranslation\Node\SafeTransNode;

class SafeTransNodeTest extends \PHPUnit_Framework_TestCase
{
    public function testCompileStrict()
    {
        $body = new \Twig_Node_Text('Hello %name%', 0);
        $vars = new \Twig_Node_Expression_Name('foo', 0);
        $node = new SafeTransNode($body, null, null, $vars);

        $env = new \Twig_Environment(null, array('strict_variables' => true));
        $compiler = new \Twig_Compiler($env);

        $this->assertEquals(sprintf(
                'echo $this->env->getExtension(\'translator\')->getTranslator()->trans("Hello %%name%%", array_merge(array("%%name%%" => twig_escape_filter($this->env, %s)), %s), "messages");',
                $this->getVariableGetter('name'),
                $this->getVariableGetter('foo')
            ),
            trim($compiler->compile($node)->getSource())
        );
    }

    protected function getVariableGetter($name)
    {
        if (version_compare(phpversion(), '5.4.0RC1', '>=')) {
            return sprintf('(isset($context["%s"]) ? $context["%s"] : $this->getContext($context, "%s"))', $name, $name, $name);
        }

        return sprintf('$this->getContext($context, "%s")', $name);
    }
}
