<?php
declare(strict_types=1);

namespace App\BlackList;

class BlackList
{
    private const LISTS_DIR = __DIR__ . '/../../blocklist-ipsets';

    /**
     * @var string[]
     */
    private array $list = [];

    /**
     * @var TextFile[]
     */
    private array $fileLists;

    /**
     * @param string[] $names
     */
    public function __construct(array $names)
    {
        foreach ($names as $name) {
            $this->fileLists[] = new TextFile(self::LISTS_DIR . '/' . $name);
        }
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
        $n = 1;
        echo "$n\n";$n++;
        while ($generator = array_shift($this->fileLists)) {
            $generator->rewind();
            while ($generator->valid()) {
                $line = $generator->current();
                echo "$line a\n";$n++;
                if (is_bool($line)) {
                    continue;
                }
                echo "$n g\n";$n++;
                $entry = $this->getEntry($line);
                if (null === $entry) {
                    continue;
                }
                echo "$n a\n";$n++;
                if (in_array($entry, $this->list)) {
                    continue;
                }
                echo "$n qq\n";$n++;
                $this->list[] = $entry;
                $generator->next();
                echo "$n\n";$n++;
            }
            unset ($fileList);
        }
    }

    /**
     * @param string $line
     * @return string|null
     */
    private function getEntry(string $line): ?string
    {
        if (!$this->isCorrectIpset($line)) {
            return null;
        }
        return $this->getFirewallRule($line);
    }

    /**
     * @param string $line
     * @return bool
     */
    private function isCorrectIpset(string $line): bool
    {
        if (preg_match("~^(?'ip'.+)/?(?'subnet'[^/]+)?$~", $line, $m)) {
            $ip = $m['ip'];
            $subnet = $m['subnet'] ?? null;
            $isSubnetOk = is_numeric($subnet) || null === $subnet;

            return $isSubnetOk && ip2long($ip);
        }

        return false;
    }

    /**
     * @param string $ipset
     * @return string
     */
    private function getFirewallRule(string $ipset): string
    {
        return sprintf('add route %s', $ipset);
    }
}
