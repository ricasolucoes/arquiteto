<?php

namespace Arquiteto\Contracts;

use Arquiteto\Contracts\Traits\ManipuleFile;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class AbstractGeneratorCommand extends Command
{
    use ManipuleFile;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * MakeEloquentFilter constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }
}
