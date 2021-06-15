<?php

declare(strict_types=1);

namespace Arquiteto\Contracts\Traits;

use Support\Patterns\Parser\ClassReader;
use Pedreiro\Exceptions\SetterGetterException;
use Support\Patterns\Parser\ComposerParser;
use Illuminate\Filesystem\Filesystem;


use Support\Patterns\Parser\ParseModelClass;

/**
 * https://github.com/usmanhalalit/GetSetGo
 */
trait ManipuleFile
{
    
    protected $composerParser = false;

    /**
     * 
     * @return void
     */
    protected function commentRules()
    {

        $this->comment($this->rules());

        if (!$this->confirm('Are you happy to proceed? [yes|no]')) {
            $this->error('Error: User is a chicken.');
            exit();
        }
    }
    protected function getComposerParser()
    {
        if (!$this->composerParser) {
            $this->composerParser = resolve(ComposerParser::class);
        }
        return $this->composerParser;
    }
    protected function getNamespaceFromFilePath($filePath)
    {
        return $this->getComposerParser()->getNamespaceFromFilePath($filePath);
    }


    protected function getNamespacePath($name)
    {
        [
            $className,
            $namespaceClassName
        ] = $this->getClassAndNamespace($name);
    
        $namespacePure = str_replace(
            $this->getComposerParser()->getNamespaceFromClass($name),
            '',
            $namespaceClassName
        );
    
        return explode(
            str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                $namespacePure
            ),
            $this->getComposerParser()->getFilePathFromClass($name)
        )[0];
    }

    protected function getClassAndNamespace($name)
    {
        $parts = array_map('studly_case', explode('\\', $name));
        
        return [
            array_pop($parts),
            $namespaceClassName = count($parts) > 0 ? implode('\\', $parts).'\\' : ''
        ];

    }

    protected function getPathForMigration($name)
    {

        $namespacePath = $this->getNamespacePath($name);
        $migrationLocationPath = false;
        foreach (
            [
                $namespacePath.'../database',
                $namespacePath.'Migrations'
            ] as $path
        ) {
            if ($this->files->exists($path)) {
                $migrationLocationPath = $path;
            }
        }
        if (!$migrationLocationPath) {
            $this->error("\n\n\tPasta para migration nao localizada!"."\n");
            die;
        }
    
        return $migrationLocationPath;
    }

    protected function getParserClass($nameClass): ParseModelClass
    {
        $parserModelClass = new ParseModelClass($nameClass);
        if (!$parserModelClass->typeIs('model')) {
            $this->error("\n\n\tClass nao eh um modelo!"."\n");
            die;
        }
        return $parserModelClass;
    }

}
