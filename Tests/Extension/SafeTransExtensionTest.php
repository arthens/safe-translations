<?php
namespace Arthens\SafeTranslation\Tests\Extension;

use Arthens\SafeTranslation\Extension\SafeTransExtension;
use Arthens\SafeTranslation\TokenParser\SafeTransChoiceTokenParser;
use Arthens\SafeTranslation\TokenParser\SafeTransTokenParser;

class SafeTransExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $extension = new SafeTransExtension();

        $this->assertTrue($extension instanceof \Twig_Extension);

        // filters
        $filters = $extension->getFilters();
        $this->assertTrue(array_key_exists("unescaped", $filters));

        // tags
        $tokens = $extension->getTokenParsers();
        $this->assertTrue($tokens[0] instanceof SafeTransTokenParser);
        $this->assertTrue($tokens[1] instanceof SafeTransChoiceTokenParser);
    }
}
