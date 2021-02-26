<?php
declare(strict_types=1);

namespace App\BlackList;

use Iterator;

class TextFile implements Iterator
{
    /**
     * @var false|resource
     */
    protected $fileHandler;

    /**
     * @var int
     */
    protected int $key;

    /**
     * @var string
     */
    protected string $currentLine;

    /**
     * @var string
     */
    protected string $fileName;

    /**
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileHandler = fopen($fileName, 'r');

        $this->fileName = $fileName;
        $this->key = 0;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->currentLine;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        if ( $this->valid() ){
            $this->currentLine = fgets($this->fileHandler );
            $this->key++;
        }
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return !feof( $this->fileHandler );
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->__destruct();
        $this->__construct( $this->fileName );
    }

    protected function __destruct()
    {
        fclose( $this->fileHandler );
    }
}
