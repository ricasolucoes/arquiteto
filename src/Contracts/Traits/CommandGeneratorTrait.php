<?php

declare(strict_types=1);

namespace Arquiteto\Contracts\Traits;

use Support\Patterns\Parser\ClassReader;
use Pedreiro\Exceptions\SetterGetterException;
use Support\Patterns\Parser\ComposerParser;

/**
 * https://github.com/usmanhalalit/GetSetGo
 */
trait CommandGeneratorTrait
{
    use ManipuleFile;
}
