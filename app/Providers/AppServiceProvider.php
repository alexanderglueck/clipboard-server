<?php

namespace App\Providers;

use App\Markdown;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Markdown::class, function () {
            $config = [
                'allow_unsafe_links' => false,
                'heading_permalink' => [
                    'html_class' => 'heading-permalink',
                    'id_prefix' => 'heading',
                    'fragment_prefix' => 'heading',
                    'insert' => 'after',
                    'min_heading_level' => 1,
                    'max_heading_level' => 6,
                    'title' => 'Permalink',
                    'symbol' => HeadingPermalinkRenderer::DEFAULT_SYMBOL,
                ],
                'external_link' => [
                    'internal_hosts' => str_replace(['http://', 'https://', ':8080'], '', config('app.url')),
                ],
            ];

            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new GithubFlavoredMarkdownExtension());
            $environment->addExtension(new ExternalLinkExtension());
            $environment->addExtension(new HeadingPermalinkExtension());
            $environment->addRenderer(FencedCode::class, new FencedCodeRenderer(['php', 'bash', 'html', 'js']));
            $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer(['php', 'bash', 'html', 'js']));

            return new Markdown(new MarkdownConverter($environment));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') === 'local') {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
                );
            });
        }
    }
}
