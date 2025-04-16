<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Scheduler;

use EasyDocLabs\Library;

/**
 * Job behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 *
 * @method void run(JobInterface $job)
 */
class ControllerBehaviorLoggable extends Library\ControllerBehaviorAbstract
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'log_file'     => null,
            'maximum_size' => 1048576*10,
        ]);

        parent::_initialize($config);
    }

    protected function _afterDispatch(Library\ControllerContextInterface $context)
    {
        $file = $this->getConfig()->log_file;

        if ($file && $context->getLogs()) {
            try {
                $this->_createFile($file);

                foreach ($context->getLogs() as $log) {
                    // Date in https://en.wikipedia.org/wiki/Common_Log_Format
                    $time = gmdate('d/M/Y:H:i:s O', $log[1]);

                    file_put_contents($file, '['.$time.'] '.$log[0]."\n", FILE_APPEND);
                }

                $this->_trimFile($file, $this->getConfig()->maximum_size);

            } catch (\Exception $e) {
                if (\Foliokit::isDebug()) throw $e;
            }
        }

    }

    /**
     * File header to restrict direct PHP access
     * @return string
     */
    protected function _getFileHeader()
    {
        return "#\n#<?php die; ?>\n";
    }

    /**
     * Create the log file
     *
     * @param $path
     */
    protected function _createFile($path)
    {
        if (!file_exists($path)) {
            file_put_contents($path, $this->_getFileHeader());
        }
    }

    /**
     * Trims a file to the given size from the beginning
     * @param $path
     * @param $maximum_size
     */
    protected function _trimFile($path, $maximum_size)
    {
        if (file_exists($path) && filesize($path) > $maximum_size) {
            $keep = filesize($path) - $maximum_size;
            $new_file = $path.'_trimmed';
            if (\file_exists($new_file)) {
                \unlink($new_file);
            }
            $this->_createFile($new_file);

            $old = fopen($path,'r+');
            $new = fopen($new_file,'a');

            fseek($old, $keep);
            fgets($old); // burn the first line so we are at a proper cut off point

            while (($buffer = fgets($old, 4096)) !== false) {
                fwrite($new, $buffer);
            }

            fclose($old);
            fclose($new);

            rename($new_file, $path);
        }
    }
}