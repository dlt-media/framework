<?php

namespace Framework\Filesystem;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * The class Filesystem provides utility functions for handling files and directories.
 *
 * @package Framework\Filesystem
 */
class Filesystem
{
    /**
     * Get files from a directory matching a specific extension.
     *
     * @param string $directory The directory path.
     * @param string|array|null $extension [optional] The extension to filter by. If null, returns all files.
     * @return array An array of file paths.
     */
    public function files(string $directory, $extension = null): array
    {
        return self::get_paths($directory, fn($file) => $file->isFile() && self::has_extension($file, $extension));
    }

    /**
     * Retrieve all files and directories within a directory.
     *
     * @param string $directory The directory path.
     * @param bool $recursive [optional] Whether to include subdirectories recursively.
     * @return array An array containing the paths of files and directories.
     */
    public function all_files(string $directory, bool $recursive = true): array
    {
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        } else {
            $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
        }

        $paths = [];
        foreach ($iterator as $file) {
            $paths[] = $file->getPathname();
        }

        return $paths;
    }

    /**
     * Write content to a file.
     *
     * @param string $file_path The path to the file.
     * @param string $content The content to write to the file.
     * @return bool true if the content was successfully written to the file, false otherwise.
     */
    public function put(string $file_path, string $content): bool
    {
        return file_put_contents($file_path, $content);
    }

    /**
     * Get directories within a directory.
     *
     * @param string $directory The directory path.
     * @return array An array of directory paths.
     */
    public function directories(string $directory): array
    {
        return self::get_paths($directory, fn($file) => $file->isDir());
    }

    /**
     * Retrieve paths from a directory based on a callback condition.
     *
     * @param string $directory The directory path.
     * @param callable $callback The callback function defining the condition.
     * @return array An array of paths that meet the condition.
     */
    private function get_paths(string $directory, callable $callback): array
    {
        $paths = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($callback($file)) {
                $paths[] = $file->getPathname();
            }
        }

        return $paths;
    }

    /**
     * Check if a file has a specific extension.
     *
     * @param SplFileInfo $file The file object.
     * @param string|array $extension The extension(s) to check against.
     * @return bool True if the file has the specified extension, false otherwise.
     */
    private function has_extension(SplFileInfo $file, $extension): bool
    {
        if (is_null($extension)) {
            return true;
        }

        $file_extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);

        return in_array($file_extension, is_array($extension) ? $extension : [$extension]);
    }

    /**
     * Delete one or multiple files or directories.
     *
     * @param string|array $paths The path(s) to the file(s) or directory(s) to delete.
     * @return bool True if all paths were successfully deleted, false otherwise.
     */
    public function delete($paths): bool
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        $success = true;

        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $success = $success && self::delete_directory($path);
                } else {
                    $success = $success && unlink($path);
                }
            } else {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $directory The directory to delete.
     * @return bool True if successfully deleted, false otherwise.
     */
    private function delete_directory(string $directory): bool
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        return rmdir($directory);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $file_path The path to the file.
     * @return string|false The contents of the file, or false on failure.
     */
    public function get(string $file_path)
    {
        return file_get_contents($file_path);
    }

    /**
     * Check if a file or directory exists.
     *
     * @param string $path The path to the file or directory.
     * @return bool True if the file or directory exists, false otherwise.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Create a directory.
     *
     * @param string $directory The directory path to create.
     * @return bool true on success, false on failure.
     */
    public function make_directory(string $directory): bool
    {
        return mkdir($directory, 0777, true);
    }
}