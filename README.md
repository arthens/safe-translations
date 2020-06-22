# Safe Translations

`arthens/safe-translations` is an extra security layer on top of [Symfony Translations](http://symfony.com/doc/current/book/translation.html).

[![Build Status](https://travis-ci.org/arthens/safe-translations.svg?branch=master)](https://travis-ci.org/arthens/safe-translations)

## Compatibility

Version 1.0 is PHP 7.* and Twig 2.* only.

Version 0.4 is the last version supporting PHP 5.* and Twig 1.*

## The problem

[Twig](http://twig.sensiolabs.org/) is a great rendering library, and it's also awesome for protecting against
[XSS](http://en.wikipedia.org/wiki/Cross-site_scripting) because all input is automatically escaped.
For example, if you have the following template:

`Hello %username%`

and the user sets their username to

`<script>alert();</script>`

you can sleep safe, because Twig will automatically escape to:

`Hello &lt;script&gt;alert();&lt;/script&gt;`

which is harmless.

### So what's the problem?

The problem is that when using Symfony Translations you lose this protection. The Twig template:

`{% trans %}Hello %username%{% endtrans %}`

is not safe to use because `username` will not be automatically escaped. You have to escape it yourself:

`{% trans with {'%username%': username|e} %}Hello %username%{% endtrans %}`

which means that your templates are unsecure by default, and it's now your responsability to remember to escape variables
every time you use them. Not the end of the world, but wouldn't it be better if variables were automatically escaped like in Twig?

Note: this problem only applies to tokens. If you use the `|trans` filter then you are ok, because everything is escaped
(unless you also use `|raw`, in that case you have a problem). See [documentation note](https://symfony.com/doc/current/translation.html#translation-filters).

## My solution

`arthens/safe-translations` defines 2 new Twig tokens: `{% safetrans %}` and `{% safetranschoice %}`.
They work exactly like `{% trans %}` and `{% transchoice %}`, but variables are automatically escaped:

`{% safetrans %}Hello %username%{% endsafetrans %}`

will once again produce

`Hello &lt;script&gt;alert();&lt;/script&gt;`

###  But what if I need to mix escaped and unescaped variables (e.g. inject HTML)?

You can, you just have to use `|unescaped`:

`{% trans with {'%message%': message|unescaped} %}Hello %username%, admin says: %message%{% endtrans %}`

In this case `username` is escaped, and `message` is not.

## Installation

- Add `arthens/safe-translations` to your `composer.json`.
- Register `Arthens\SafeTranslation\Extension\SafeTransExtension` in your `Twig_Enviroment`.

and you should be good to go (assuming that you have already configured Symfony Translations).

## FAQ

#### 1. How can I automatically escape the variables when using Symfony Translations with Twig?

Use `{% safetrans %}` and `{% safetranschoice %}`.

#### 2. What options do `safetrans` and `safetranschoice` support?

They are built on top of Symfony Translations, and they support iexactly the same options.
See [Symfony Translations](http://symfony.com/doc/current/book/translation.html)

#### 3. How do I extract the strings from my template?

Use the standard Symfony Extractor. (Under the hood `arthens/safe-translations` extends Symfony's `TransNode`,
which means that from the point of view of the extractor there's no different between `trans` and `safetrans`).

#### 4. Why do I need to use `|unescaped`? Can't it guess it from the context?

Not yet. Symfony Translations and Twig are quite different, and I couldn't find a way to do it automagically.
This might change with future versions. Pull requests are welcome.

#### 5. Is this production ready?

[99designs](https://99designs.com) has been using it in production since 2013.
