<?php
namespace Arthens\SafeTranslation\Tests\TokenParser;

use Arthens\SafeTranslation\Extension\SafeTransExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

class SafeTransChoiceTokenParserTest extends \PHPUnit_Framework_TestCase
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
            '{0} Hello %name%|{1} Goodbye %name%' => '{0} Ciao %name%|{1} Arrivederci %name%',
        ), 'it', 'dom');

        $loader = new \Twig_Loader_String();
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension(new TranslationExtension($this->translator));
        $this->twig->addExtension(new SafeTransExtension());
    }

    /**
     * Check that enabling SafeTransExtension does not change the behavior of {% transchoice %}
     */
    public function testTransChoice()
    {
        $template = "{% transchoice count %}{0} Hello %name%|{1} Goodbye %name%{% endtranschoice %}";

        $html = $this->twig->render($template, array(
            'count' => 0,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello <script>alert();</script>", $html);

        $html = $this->twig->render($template, array(
            'count' => 1,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Goodbye <script>alert();</script>", $html);
    }

    public function testSafeTransChoice()
    {
        $template = "{% safetranschoice count %}{0} Hello %name%|{1} Goodbye %name%{% endsafetranschoice %}";

        $html = $this->twig->render($template, array(
            'count' => 0,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;", $html);

        $html = $this->twig->render($template, array(
            'count' => 1,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Goodbye &lt;script&gt;alert();&lt;/script&gt;", $html);
    }

    public function testSafeTransChoiceAndWith()
    {
        $template = "{% safetranschoice count with {'%name%': name} %}{0} Hello %name%|{1} Goodbye %name%{% endsafetranschoice %}";

        $html = $this->twig->render($template, array(
            'count' => 0,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;", $html);

        $html = $this->twig->render($template, array(
            'count' => 1,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Goodbye &lt;script&gt;alert();&lt;/script&gt;", $html);
    }

    public function testSafeTransChoiceAndWithNoEscape()
    {
        $template = "{% safetranschoice count with {
            '%name%': name,
            '%what%': what|unescaped,
            } %}{0} Hello %name%, you are %what%|{1} Goodbye %name%, you are %what%{% endsafetranschoice %}";

        $html = $this->twig->render($template, array(
            'count' => 0,
            'name' => "<script>alert();</script>",
            'what' => "<b>awesome</b>",
        ));
        $this->assertEquals("Hello &lt;script&gt;alert();&lt;/script&gt;, you are <b>awesome</b>", $html);

        $html = $this->twig->render($template, array(
            'count' => 1,
            'name' => "<script>alert();</script>",
            'what' => "<b>awesome</b>",
        ));
        $this->assertEquals("Goodbye &lt;script&gt;alert();&lt;/script&gt;, you are <b>awesome</b>", $html);
    }

    public function testSafeTransChoiceAndDomain()
    {
        $template = "{% safetranschoice count from 'dom' into 'it' %}{0} Hello %name%|{1} Goodbye %name%{% endsafetranschoice %}";

        $html = $this->twig->render($template, array(
            'count' => 0,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Ciao &lt;script&gt;alert();&lt;/script&gt;", $html);

        $html = $this->twig->render($template, array(
            'count' => 1,
            'name' => "<script>alert();</script>",
        ));
        $this->assertEquals("Arrivederci &lt;script&gt;alert();&lt;/script&gt;", $html);
    }
}
