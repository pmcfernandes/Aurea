<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc\Fixtures;

use Aurea\Mvc\TController;

/** Controller cujo before() cancela sempre a ação. */
class BlockerController extends TController
{
    protected function before(): mixed
    {
        return false;
    }

    public function onIndex(): string
    {
        return 'never';
    }
}
