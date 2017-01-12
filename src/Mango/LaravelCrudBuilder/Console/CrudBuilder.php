<?php namespace Mango\LaravelCrudBuilder\Console;

use File;

class CrudBuilder
{
    public function replaceVars($vars, $content, $file)
    {
        $fileContent = File::get($file);

        File::put($file, str_replace($vars, $content, $fileContent));
    }

    public function copyFile($old, $new)
    {
        return !File::copy($old, $new);
    }
}