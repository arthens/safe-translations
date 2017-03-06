<?php
namespace Arthens\SafeTranslation\Node;

use Arthens\SafeTranslation\Extension\SafeTransExtension;
use Symfony\Bridge\Twig\Node\TransNode;
use Twig_Node_Expression_Filter as Filter;

class SafeTransNode extends TransNode
{
    /*
     * Re-define compileString to automatically escape variables, unless they have been marked as |unescaped
     */
    protected function compileString(\Twig_Node $body, \Twig_Node_Expression_Array $vars, $ignoreStrictCheck = false)
    {
        // Extract the message to be translated, or return if it doesn't need translations
        if ($body instanceof \Twig_Node_Expression_Constant) {
            $msg = $body->getAttribute('value');
        } elseif ($body instanceof \Twig_Node_Text) {
            $msg = $body->getAttribute('data');
        } else {
            return array($body, $vars);
        }

        // Escape variable passed using 'with'
        // {% safetrans with {...} %}{% endsafetrans %}
        $vars = $this->escapeWithVariables($vars, $body->getTemplateLine());

        // Escape variables that needs to be read from $context
        // {% safetrans %}...{% endsafetrans %}
        $vars = $this->escapeContextVariables($msg, $vars, $body->getTemplateLine());

        return array(
            new \Twig_Node_Expression_Constant(str_replace('%%', '%', trim($msg)), $body->getTemplateLine()),
            $vars
        );
    }

    /**
     * Escapes all the variables passed using 'with'
     *
     * @param  \Twig_Node_Expression_Array $vars
     * @param  int                         $lineno
     * @return \Twig_Node_Expression_Array
     */
    protected function escapeWithVariables($vars, $lineno)
    {
        $pairs = $vars->getKeyValuePairs();
        $vars = new \Twig_Node_Expression_Array(array(), $lineno);
        foreach ($pairs as $pair) {
            $vars->addElement(
                $this->escapeNodeIfNecessary($pair['value'], $lineno),
                $pair['key']
            );
        }

        return $vars;
    }

    /**
     * @param  string                      $msg
     * @param  \Twig_Node_Expression_Array $vars
     * @param  int                         $lineno
     * @return \Twig_Node_Expression_Array
     */
    protected function escapeContextVariables($msg, $vars, $lineno)
    {
        preg_match_all('/(?<!%)%([^%]+)%/', $msg, $matches);

        foreach ($matches[1] as $var) {
            $key = new \Twig_Node_Expression_Constant('%'.$var.'%', $lineno);
            if (!$vars->hasElement($key)) {
                $node = new \Twig_Node_Expression_Name($var, $lineno);
                $node = $this->escapeNodeIfNecessary($node, $lineno);

                $vars->addElement($node, $key);
            }
        }

        return $vars;
    }

    /**
     * Wraps the node with an 'escape' filter unless it's already wrapped by an 'unescaped' filter
     *
     * @param  \Twig_NodeInterface          $node
     * @param  int                          $lineno
     * @return \Twig_Node_Expression_Filter
     */
    protected function escapeNodeIfNecessary($node, $lineno)
    {
        if (self::hasNoEscapeFilter($node)) {
            // The node was marked as safe using |unescaped
            return $node;
        } else {
            // Wrap the node with an escape filter
            return new \Twig_Node_Expression_Filter(
                $node,
                new \Twig_Node_Expression_Constant("escape", $lineno),
                new \Twig_Node_Expression_Array(array(), $lineno),
                $lineno
            );
        }
    }

    /**
     * @param \Twig_NodeInterface $node
     * @return bool
     */
    protected static function hasNoEscapeFilter($node)
    {
        return $node instanceof Filter && SafeTransExtension::DO_NOT_ESCAPE == $node->getNode('filter')->getAttribute('value');
    }
}
