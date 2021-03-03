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
        while ($generator = array_shift($this->fileLists)) {
            $generator->rewind();
            while ($generator->valid()) {
                $line = $generator->current();
                if (is_bool($line)) {
                    $generator->next();
                    continue;
                }
                $entry = $this->getEntry($line);
                if (null === $entry) {
                    $generator->next();
                    continue;
                }
                if (in_array($entry, $this->list)) {
                    $generator->next();
                    continue;
                }
                $this->list[] = $entry;
                $generator->next();
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
        if (preg_match("/^(?'ip'(?:[0-9]{1,3}\.){3}[0-9]{1,3})\/?(?'subnet'\d+)?$/", $line, $m)) {
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
        return sprintf('add list=blacklist address=%s', $ipset);
    }
}
