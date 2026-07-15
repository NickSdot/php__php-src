--TEST--
Markup statements: semicolonless accumulation in Markup\Html return-typed functions
--EXTENSIONS--
markup
--FILE--
<?php
declare(strict_types=1);

use Markup\Html as HtmlAlias;

class OtherMarkupReturn {}

class Greeting implements Markup\Html
{
    public function __construct(public string $name) {}

    public function toHtml(): Markup\Html
    {
        <h1 class="title">Hello, {$this->name}!</h1>
        <p>foo</p>
    }
}

echo new Greeting('Nick & Co'), "\n";

function panel(bool $show): Markup\Html
{
    <section>
        <h2>Top</h2>
    </section>

    if ($show) {
        <p>shown</p>
    }

    foreach (['a', 'b'] as $value) {
        <span>{$value}</span>
    }
}

echo panel(true), "\n";

function alias_return(): HtmlAlias
{
    <small>alias</small>
}

echo alias_return(), "\n";

function nullable_return(): ?Markup\Html
{
    <small>nullable</small>
}

echo nullable_return(), "\n";

function builtin_union_return(): string|Markup\Html
{
    <small>union</small>
}

echo builtin_union_return(), "\n";

function class_union_return(): OtherMarkupReturn|Markup\Html
{
    <small>class-union</small>
}

echo class_union_return(), "\n";

function after_if(bool $show): Markup\Html
{
    if ($show) {
        <em>inside</em>
    }

    <p>after</p>
}

echo after_if(true), "\n";

function empty_when_false(bool $show): Markup\Html
{
    if ($show) {
        <i>hit</i>
    }
}

echo '[', empty_when_false(false), "]\n";

function explicit_return_wins(): Markup\Html
{
    <p>before</p>
    return <strong>override</strong>;
    <p>after</p>
}

echo explicit_return_wins(), "\n";

class Hello implements Markup\Html
{
    public function __construct(public string $name) {}

    public function toHtml(): Markup\Html
    {
        $foo = [Hello::class, "2"];
        <h1 class="title">Hello, {$this->name}!</h1>

        Hello::class;
        [$one, $two] = $foo;
        <p>{$one}: {$two}</p>

        if(Hello::class === $one) {
            <p>It was indeed {$one}</p>
        }

        if("2" === $two):
            <p>And two was {$two}</p> // could we get this work without the wrapping html element?
        endif;

        echo \PHP_EOL . Hello::class . \PHP_EOL;
        echo "Hey";
    }
}

echo new Hello('Nick & Co'), "\n";

class LabelValue
{
    public function __construct(public string $prop) {}
}

class InterfaceTypedExamples implements Markup\Html
{
    public function __construct(private array $values) {}

    public function toHtml(): Markup\Html
    {
        $object = new LabelValue('from new & property');
        <p>{$object->prop}</p>

        $anonymous = new class('anonymous value') {
            public function __construct(public string $prop) {}
        };
        <p>{$anonymous->prop}</p>

        $wrap = function (string $value): Markup\Html {
            <span>{$value}</span>
        };
        <div>{array_map($wrap, $this->values)}</div>

        $make = fn (string $value): LabelValue => new LabelValue($value);
        $made = $make('from callback new');
        <p>{$made->prop}</p>
    }
}

echo new InterfaceTypedExamples(['a & b', 'c']), "\n";

eval('function missing_markup_return_type() { <p>nope</p> }');
?>
--EXPECTF--
<h1 class="title">Hello, Nick &amp; Co!</h1><p>foo</p>
<section><h2>Top</h2></section><p>shown</p><span>a</span><span>b</span>
<small>alias</small>
<small>nullable</small>
<small>union</small>
<small>class-union</small>
<em>inside</em><p>after</p>
[]
<strong>override</strong>
<h1 class="title">Hello, Nick &amp; Co!</h1><p>Hello: 2</p><p>It was indeed Hello</p><p>And two was 2</p>
Hello
Hey
<p>from new &amp; property</p><p>anonymous value</p><div><span>a &amp; b</span><span>c</span></div><p>from callback new</p>

Fatal error: Markup statements are only allowed in functions returning Markup\Html in %simplicit_markup_body.php(%d) : eval()'d code on line 1
