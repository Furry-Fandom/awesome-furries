<?php
declare(strict_types=1);
namespace Furry\Pawesome;

/**
 * Class Builder
 * @package Furry\Pawesome
 */
class Builder
{
    /** @var string $baseUrl */
    protected $baseUrl = 'https://github.com/Furry-Fandom/awesome-furries/blob/master/furries/';

    /** @var string $index */
    protected $indexFile = '';

    /** @var string $root */
    protected $root;

    /** @var string $preamble */
    protected $preamble = '';

    /** @var string $suffix */
    protected $suffix = '';

    /**
     * Builder constructor.
     * @param string $root
     * @param string $indexFile
     */
    public function __construct(string $root = '', string $indexFile = 'index.json')
    {
        $this->indexFile = $indexFile;
        if (!$root) {
            $root = dirname(__DIR__);
        }
        $this->root = $root;
    }

    public function setGithubBase(string $url): self
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * @param string $src
     * @return self
     * @throws \Exception
     */
    public function loadPreambleFile(string $src = 'PREAMBLE.md'): self
    {
        $contents = file_get_contents($this->root . '/' . $src);
        if (!is_string($contents)) {
            throw new \Exception('Could not load preamble file');
        }
        $this->preamble = $contents;
        return $this;
    }

    /**
     * @param string $src
     * @return self
     * @throws \Exception
     */
    public function loadSuffixFile(string $src = 'SUFFIX.md'): self
    {
        $contents = file_get_contents($this->root . '/' . $src);
        if (!is_string($contents)) {
            throw new \Exception('Could not load preamble file');
        }
        $this->suffix = $contents;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function loadIndexFile(): array
    {
        $contents = file_get_contents($this->root . '/' . $this->indexFile);
        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new \Exception('Could not load index file');
        }
        return $decoded;
    }

    /**
     * @param string $outFile
     * @return bool
     * @throws \Exception
     */
    public function build(string $outFile = 'README.md'): bool
    {
        $index = $this->loadIndexFile();
        $headlines = $index['meta'];
        $collection = $index['furries'];
        $readMe = $this->preamble;
        foreach ($collection as $subgroup => $furries) {
            if (isset($headlines[$subgroup])) {
                $readMe .= "\n{$headlines[$subgroup]}\n";
            } else {
                $readMe .= PHP_EOL . '## ' . ucfirst($subgroup) . PHP_EOL;
            }
            $readMe .= PHP_EOL;

            foreach ($furries as $furry) {
                if (!isset($furry['name'], $furry['path'])) {
                    echo 'Warning: Not enough metdata!', PHP_EOL;
                    continue;
                }
                $readMe .= ' * [' . $furry['name'] . '](' . $this->baseUrl . $furry['path'] . ')';
                if (!empty($furry['summary'])) {
                    $readMe .= ' - ' . $furry['summary'];
                }
                $readMe .= PHP_EOL;
            }
            $readMe .= PHP_EOL;
        }
        $readMe .= $this->suffix;
        $written = file_put_contents(
            $this->root . '/' . $outFile,
            $readMe
        );
        return !is_bool($written);
    }
}
