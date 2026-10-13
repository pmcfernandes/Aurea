<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc\Fixtures;

use Aurea\Http\THttpResponse;
use Aurea\Mvc\TController;

/** Controller stub para os testes do TRouter/TController. */
class HomeController extends TController
{
    /** @var string[] */
    public static array $calls = [];

    protected function before(): mixed
    {
        self::$calls[] = 'before';
        return null;
    }

    protected function after(): void
    {
        self::$calls[] = 'after';
    }

    public function onIndex(): string
    {
        self::$calls[] = 'index';
        return 'home-html';
    }

    public function onShow(): string
    {
        return 'show:' . ($this->route_params['name'] ?? '?');
    }

    public function onPing(): THttpResponse
    {
        return $this->json(['pong' => true]);
    }
}
