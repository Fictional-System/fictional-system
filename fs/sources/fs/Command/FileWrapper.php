<?php

namespace Command;

use RuntimeException;

class FileWrapper
{
  const IGNORED_ROOT_FILES = [
    '.',
    '..',
    '.git',
    '.github',
    '.idea',
    'bin',
    'fs',
  ];

  const IGNORED_FILES = [
    '.',
    '..',
    '.git',
  ];

  public function __construct(private string $cwd)
  {
  }

  public function copy(string $sourcePath, string $targetPath, bool $recursive = false, array $except = [])
  {
    if (!$this->exists($sourcePath))
    {
      throw new RuntimeException("`$sourcePath` does not exist.");
    }

    if ($this->isDir($sourcePath))
    {
      if (!$this->exists($targetPath))
      {
        if (!$recursive)
        {
          throw new RuntimeException("`$targetPath` does not exist.");
        }
        $this->mkdir($targetPath);
      }

      $except = array_merge(['.', '..'], $except);
      foreach ($this->scandir($sourcePath) as $p)
      {
        if (in_array($p, $except))
          continue;

        if ($this->isDir("$sourcePath/$p"))
        {
          $this->copy("$sourcePath/$p", "$targetPath/$p", $recursive, $except);
        }
        else
        {
          $this->copyFile("$sourcePath/$p", "$targetPath/$p");
        }
      }
    }
    else
    {
      $this->copyFile($sourcePath, $targetPath);
    }
  }

  public function cleanDir(string $path = '.', array $except = []): void
  {
    if (!$this->fileExists($path) || !$this->isDir($path))
    {
      return;
    }

    $except = array_merge(['.', '..'], $except);
    foreach ($this->scandir($path) as $p)
    {
      if (in_array($p, $except))
        continue;

      if ($this->isDir("$path/$p"))
      {
        $this->cleanDir("$path/$p", $except);
        $this->rmdir("$path/$p");
      }
      else
      {
        $this->unlink("$path/$p");
      }
    }
  }

  public function exists(string $path): bool
  {
    return file_exists($this->absolutePath($path));
  }

  public function fileGetContent(string $path): void
  {
    if (@file_get_contents($this->absolutePath($path)) === false)
    {
      throw new RuntimeException("Unable to get file `$path`.");
    }
  }

  public function filePutContent(string $path, string $content, int $flags = 0): void
  {
    if (@file_put_contents($this->absolutePath($path), $content, $flags) === false)
    {
      throw new RuntimeException("Unable to put file `$path`.");
    }
  }

  public function fileExists(string $path): bool
  {
    return file_exists($this->absolutePath($path));
  }

  public function absolutePath(string $path): string
  {
    return $this->cwd . '/' . $path;
  }

  public function mkdir(string $path): void
  {
    if ($this->fileExists($path))
    {
      if (!$this->isDir($path))
      {
        throw new RuntimeException("Unable to create `$path`. It already exist as a file.");
      }
      return;
    }

    if (@mkdir($this->absolutePath($path), 0700, true) === false)
    {
      throw new RuntimeException("Unable to create `$path`.");
    }
  }

  public function rmdir(string $path): void
  {
    if (@rmdir($this->absolutePath($path)) === false)
    {
      throw new RuntimeException("Unable to delete `$path`.");
    }
  }

  public function unlink(string $path): void
  {
    if (@unlink($this->absolutePath($path)) === false)
    {
      throw new RuntimeException("Unable to delete `$path`.");
    }
  }

  public function scandir(string $path): array|false
  {
    return scandir($this->absolutePath($path));
  }

  public function isDir(string $path): bool
  {
    return is_dir($this->absolutePath($path));
  }

  public function copyFile(string $sourcePath, string $targetPath)
  {
    if (!@copy($this->absolutePath($sourcePath), $this->absolutePath($targetPath)))
    {
      throw new RuntimeException("Unable to copy `$sourcePath` to `$targetPath`.");
    }
  }
}
