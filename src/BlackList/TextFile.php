<?php

namespace App\BlackList;

use Iterator;
use RuntimeException;

class TextFile implements Iterator {

    /**
     * @var false|resource
     */
    protected $fileHandle;

    /**
     * @var false|string
     */
    protected $line;

    /**
     * @var int
     */
    protected $i;

    /**
     * @param string $fileName
     */
    public function __construct($fileName) {
        if (!$this->fileHandle = fopen($fileName, 'r')) {
            throw new RuntimeException('Невозможно открыть файл "' . $fileName . '"');
        }
    }

    /**
     * @return void
     */
    public function rewind() {
        fseek($this->fileHandle, 0);
        $this->line = fgets($this->fileHandle);
        $this->i = 0;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return false !== $this->line;
    }

    /**
     * @return false|string
     */
    public function current() {
        return $this->line;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->i;
    }

    /**
     * @return void
     */
    public function next() {
        if (false !== $this->line) {
            $this->line = fgets($this->fileHandle);
            $this->i++;
        }
    }

    public function __destruct() {
        fclose($this->fileHandle);
    }
}
