<?php
/**
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Liip\MonitorBundle\Check;

use InvalidArgumentException;
use Traversable;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * Validate that a given file path (or a collection of paths) is a file and is readable.
 */
class FileReadable extends AbstractCheck implements CheckInterface
{
    /**
     * @var array|Traversable
     */
    protected $file;

    /**
     * @param string|array|Traversable $path Path name or an array of paths
     *
     * @throws InvalidArgumentException
     */
    public function __construct($path)
    {
        if (is_object($path) && !$path instanceof Traversable) {
            throw new InvalidArgumentException(
                'Expected a file name (string) , an array or Traversable of strings, got '.get_class($path)
            );
        }

        if (!is_object($path) && !is_array($path) && !is_string($path)) {
            throw new InvalidArgumentException('Expected a file path (string) or an array of strings');
        }

        if (is_string($path)) {
            $this->file = array($path);
        } else {
            $this->file = $path;
        }
    }

    /**
     * Perform the check.
     *
     * @see \ZendDiagnostics\Check\CheckInterface::check()
     *
     * @return Failure|Success
     */
    public function check()
    {
        $nonFiles = $unreadable = array();

        // Check each path if it's a file and is readable
        foreach ($this->file as $file) {
            if (!is_file($file)) {
                $nonFiles[] = $file;
            }

            if (!is_readable($file)) {
                $unreadable[] = $file;
            }
        }

        // Construct failure message
        $failureString = '';
        if (count($nonFiles) > 1) {
            $failureString .= 'The following paths are not valid file: '.implode(', ', $nonFiles).' ';
        } elseif (count($nonFiles) == 1) {
            $failureString .= current($nonFiles).' is not a valid file. ';
        }

        if (count($unreadable) > 1) {
            $failureString .= 'The following files are not readable: '.implode(', ', $unreadable);
        } elseif (count($unreadable) == 1) {
            $failureString .= current($unreadable).' file is not readable.';
        }

        // Return success or failure
        if ($failureString) {
            return new Failure(trim($failureString), array('nonFiles' => $nonFiles, 'unreadable' => $unreadable));
        } else {
            return new Success(
                count($this->file) > 1 ? 'All paths are readable file.' : 'The path is a readable file.',
                $this->file
            );
        }
    }
}
