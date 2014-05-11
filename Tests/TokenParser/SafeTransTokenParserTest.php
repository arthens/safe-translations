<?php
namespace Arthens\SafeTranslation\Tests\TokenParser;

use Arthens\SafeTranslation\Extension\SafeTransExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

class SafeTransTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var Translator */
    private $translator;

    public function setUp()
    {
        $this->translator = new Translator("en_US");

        $this->translator->addLoader('array', new ArrayLoader());
        $this->translator->addResource('array', array(
            'Hello %name%' => 'Ciao %name%',
        ), 'it', 'dom');

        $loader = new \Twig_Loader_String();
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new TranslationExtension($this->translator));
        $this->twig->addExtension(new SafeTransExtension());
    }

    /**
     * Check that enabling SafeTransExtension does not change the behavior of {% trans %}
     */
    public function testTrans()
    {
        $html = $this->twig->render("{% trans %}Hello %name%{% endtrans %}", array(
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello <script>alert();</script>", $html);
    }

    public function testSafeTrans()
    {
        $html = $this->twig->render("{% safetrans %}Hello %name%{% endsafetrans %}", array(
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;", $html);
    }

    public function testSafeTransAndWith()
    {
        $template = "{% safetrans with {'%name%': name} %}Hello %name%{% endsafetrans %}";
        $html = $this->twig->render($template, array(
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;", $html);
    }

    public function testSafeTransWithNoEscape()
    {
        $template = "{% safetrans with {
            '%name%': name,
            '%what%': what|unescaped,
            } %}Hello %name%, you are %what%{% endsafetrans %}";

        $html = $this->twig->render($template, array(
            'name' => "<script>alert();</script>",
            'what' => "<b>awesome</b>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;, you are <b>awesome</b>", $html);
    }

    public function testDomain()
    {
        $html = $this->twig->render("{% safetrans from 'dom' into 'it' %}Hello %name%{% endsafetrans %}", array(
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Ciao &lt;script&gt;alert();&lt;/script&gt;", $html);
    }
}
