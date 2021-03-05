<?php

namespace App\BlackList;

class Builder
{
    const IP_ENTRY = "/^(?'ip'(?:[0-9]{1,3}\.){3}[0-9]{1,3})\/?(?'subnet'\d+)?$/";

    const BLACKLISTFULL_FILENAME = 'blacklistfull.txt';

    /**
     * @var string[]
     */
    private $list = [];

    /**
     * @var TextFile[]
     */
    private $fileLists;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string[]
     */
    private $names;

    /**
     * @param string $path
     * @param string[] $names
     */
    public function __construct($path, array $names)
    {
        $this->path = $path;
        $this->names = $names;
    }

    /**
     * @param string $fullFileName
     */
    public function buildFile($fullFileName)
    {
        $this->createFileLists();
        $this->parseFileLists();
        $this->createDiffFile($fullFileName . '.diff');
        $this->saveBlackListEntries();
        $this->saveAllBlackListRules($fullFileName . '.rsc');
    }

    private function createFileLists()
    {
        foreach ($this->names as $name) {
            $this->fileLists[] = new TextFile($this->path . $name);
        }
    }

    /**
     * @return void
     */
    private function parseFileLists()
    {
        while ($generator = array_shift($this->fileLists)) {
            $generator->rewind();
            while ($generator->valid()) {
                $line = $generator->current();
                if (is_bool($line)) {
                    $generator->next();
                    continue;
                }
                $entry = $this->getEntry(trim($line));
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

    private function createDiffFile($fullFileName)
    {
        $entriesAll = @file(self::BLACKLISTFULL_FILENAME, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (false === $entriesAll) {
            $entriesDiff = array_map([$this, 'getFirewallRuleAdd'], $this->list);
            array_unshift($entriesDiff, "/ip firewall address-list");
            $this->writeToTextFile($fullFileName, $entriesDiff);
            return;
        }
        $entriesToAdd = array_diff($this->list, $entriesAll);
        $entriesToAddRules = array_map([$this, 'getFirewallRuleAdd'], $entriesToAdd);
        $entriesToRemove = array_diff($entriesAll, $this->list);
        $entriesToRemoveRules = array_map([$this, 'getFirewallRuleRemove'], $entriesToRemove);
        $entriesDiff = array_merge($entriesToAddRules, $entriesToRemoveRules);
        if (empty($entriesDiff)) {
            $this->writeToTextFile($fullFileName, $entriesDiff);
            return;
        }
        array_unshift($entriesDiff, "/ip firewall address-list");
        $this->writeToTextFile($fullFileName, $entriesDiff);
    }

    private function saveBlackListEntries()
    {
        $this->writeToTextFile(self::BLACKLISTFULL_FILENAME, $this->list);
    }

    /**
     * @param string $fullFileName
     */
    private function saveAllBlackListRules($fullFileName)
    {
        $content = array_map([$this, 'getFirewallRuleAdd'], $this->list);
        array_unshift($content, "/ip firewall address-list");
        $this->writeToTextFile($fullFileName, $content);
    }

    /**
     * @param string $line
     * @return string|null
     */
    private function getEntry($line)
    {
        if ($this->isCorrectIpset($line)) {
            return $line;
        }
        return null;
    }

    /**
     * @param string $line
     * @return bool
     */
    private function isCorrectIpset($line)
    {
        if (preg_match(self::IP_ENTRY, $line, $m)) {
            $ip = $m['ip'];
            $subnet = isset($m['subnet']) ? $m['subnet'] : null;
            $isSubnetOk = is_numeric($subnet) || null === $subnet;
            return $isSubnetOk && ip2long($ip);
        }
        return false;
    }

    /**
     * @param string $ipset
     * @return string
     */
    private function getFirewallRuleAdd($ipset)
    {
        return sprintf('add list=blacklist address=%s', $ipset);
    }

    /**
     * @param string $ipset
     * @return string
     */
    private function getFirewallRuleRemove($ipset)
    {
        return sprintf('remove list=blacklist [find address=%s]', $ipset);
    }

    /**
     * @param $fullFileName
     * @param array $entries
     * @return false|int
     */
    private function writeToTextFile($fullFileName, array $entries)
    {
        $content = array_map(
            function ($line) {
                return $line . PHP_EOL;
            },
            $entries
        );
        return file_put_contents($fullFileName, $content);
    }
}
