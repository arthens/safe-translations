<?php
namespace Arthens\SafeTranslation\TokenParser;

use Arthens\SafeTranslation\Node\SafeTransNode;
use Symfony\Bridge\Twig\Node\TransNode;
use Symfony\Bridge\Twig\TokenParser\TransChoiceTokenParser;

class SafeTransChoiceTokenParser extends TransChoiceTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param  \Twig_Token         $token A Twig_Token instance
     * @throws \Twig_Error_Syntax
     * @return SafeTransNode A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        // We use the parent to do the actual parsing, and we simply convert the result

        /** @var TransNode $node */
        $node = parent::parse($token);

        return new SafeTransNode(
            $node->hasNode('body') ? $node->getNode('body') : null,
            $node->hasNode('domain') ? $node->getNode('domain') : null,
            $node->hasNode('count') ? $node->getNode('count') : null,
            $node->hasNode('vars') ? $node->getNode('vars') : null,
            $node->hasNode('locale') ? $node->getNode('locale') : null,
            $node->getTemplateLine(),
            $this->getTag()
        );
    }

    /**
     * @param \Twig_Token $token
     * @return mixed
     */
    public function decideTransChoiceFork($token)
    {
        return $token->test(array('endsafetranschoice'));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'safetranschoice';
    }
}
