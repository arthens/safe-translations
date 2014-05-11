<?php
namespace Arthens\SafeTranslation\Extension;

use Arthens\SafeTranslation\TokenParser\SafeTransChoiceTokenParser;
use Arthens\SafeTranslation\TokenParser\SafeTransTokenParser;

class SafeTransExtension extends \Twig_Extension
{
    const DO_NOT_ESCAPE = 'unescaped';

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            self::DO_NOT_ESCAPE => new \Twig_Filter_Method($this, 'unescaped'),
        );
    }

    /**
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new SafeTransTokenParser(),
            new SafeTransChoiceTokenParser(),
        );
    }

    /**
     * @param mixed $val
     * @return mixed
     */
    public function unescaped($val)
    {
        // This function is used to mark value as unescaped in {% safetrans %}
        return $val;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "safe_translations";
    }
}
