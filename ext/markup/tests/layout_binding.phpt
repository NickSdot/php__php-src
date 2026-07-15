--TEST--
Markup layout binding: render a statement-list body with an existing object as $this
--EXTENSIONS--
markup
--FILE--
<?php
declare(strict_types=1);

final class Page implements Markup\Html
{
    public function __construct(
        public string $title,
        public array $items,
    ) {}

    public function subtitle(): string
    {
        return 'Sub: ' . $this->title;
    }

    public function toHtml(): Markup\Html
    {
        return <article data-title={$this->title}>
            <header>{$this->title}</header>
            {$this->renderable}
        </article>;
    }
}

$page = new Page('Dashboard & Home', ['a', 'b']);
$heading = 'Top & now';

$bound = <<{$page}>>
    <h1>{$this->title}</h1>

    <section>
        <h2>{$heading}</h2>
    </section>

    if ($this === $page) {
        <p>same object</p>
    }

    if ($this->title) {
        <p>{$this->subtitle()}</p>
    }

    foreach ($this->items as $value) {
        <span>{$value}</span>
    }

    <footer>escaped</footer>
<</>>;

echo $bound, "\n";

function render_string(Page $page): string
{
    return (string) <<{$page}>>
        <p>from function</p>
    <</>>;
}

echo render_string(new Page('Function', [])), "\n";

function make_page(int &$calls): Page
{
    $calls++;
    return new Page('Once', []);
}

$calls = 0;
$once = <<{make_page($calls)}>>
    <p>target evaluated once</p>
<</>>;

echo $once, "\n";
echo $calls, "\n";

$generic = new class('Generic') implements Markup\Renderable {
    public function __construct(public string $title) {}
};
echo <<{$generic}>>
    <p>{$this->title}</p>
<</>>, "\n";

final class MagicPage implements Markup\Html
{
    public function __get(string $name): mixed
    {
        return match ($name) {
            'footer' => \Markup\raw('<footer>magic getter</footer>'),
            default => \Markup\raw('<b>unexpected ' . $name . '</b>'),
        };
    }

    public function toHtml(): Markup\Html
    {
        return <main>
            {$this->renderable}
            {$this->footer}
        </main>;
    }
}

$magic = new MagicPage();
echo <<{$magic}>>
    <p>engine body property</p>
<</>>, "\n";

echo 1 << 2, "\n";
?>
--EXPECT--
<article data-title="Dashboard &amp; Home"><header>Dashboard &amp; Home</header><h1>Dashboard &amp; Home</h1><section><h2>Top &amp; now</h2></section><p>same object</p><p>Sub: Dashboard &amp; Home</p><span>a</span><span>b</span><footer>escaped</footer></article>
<article data-title="Function"><header>Function</header><p>from function</p></article>
<article data-title="Once"><header>Once</header><p>target evaluated once</p></article>
1
<p>Generic</p>
<main><p>engine body property</p><footer>magic getter</footer></main>
4
