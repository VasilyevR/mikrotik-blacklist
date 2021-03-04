<?php

namespace App\BlackList;

class Builder
{
    const IP_ENTRY = "/^(?'ip'(?:[0-9]{1,3}\.){3}[0-9]{1,3})\/?(?'subnet'\d+)?$/";

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
     * @param string $string
     */
    public function buildFile($string)
    {
        $this->createFileLists();
        $this->parseFileLists();
        $this->saveList($string);
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
     * @param string $fullFileName
     */
    private function saveList($fullFileName)
    {
        $content = $this->list;
        array_unshift($content, "/ip firewall address-list\n");
        file_put_contents($fullFileName, $content);
    }

    /**
     * @param string $line
     * @return string|null
     */
    private function getEntry($line)
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
    private function getFirewallRule($ipset)
    {
        return sprintf('add list=blacklist address=%s', $ipset);
    }
}
