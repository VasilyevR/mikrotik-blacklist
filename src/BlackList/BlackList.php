<?php
declare(strict_types=1);

namespace App\BlackList;

class BlackList
{
    private const LISTS_DIR = __DIR__ . 'blocklist-ipsets';

    /**
     * @var string[]
     */
    private array    $list;

    /**
     * @var TextFile
     */
    private TextFile $fileList;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->fileList = new TextFile(self::LISTS_DIR . '\\' . $name);
        $this->parse();
    }

    /**
     * @return string[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @return void
     */
    private function parse(): void
    {
        foreach ($this->fileList as $line)
        {
            if ($line)
            {
                $this->list[] = $line;
            }
        }

    }
}
