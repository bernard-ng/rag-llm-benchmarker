<?php

declare(strict_types=1);

namespace App\Service;

use LLPhant\Embeddings\Document;

/**
 * Class IteratorFileDataReader.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
class FileDataReader
{
    public string $sourceType = 'droit congolais';

    /**
     * @template T of Document
     *
     * @param  class-string<T>  $documentClassName
     */
    public function __construct(
        public readonly string $filePath,
        public readonly string $documentClassName = Document::class
    ) {
    }

    public function getDocuments(): iterable
    {
        if (is_dir($this->filePath)) {
            if ($handle = opendir($this->filePath)) {
                while (($entry = readdir($handle)) !== false) {
                    $fullPath = $this->filePath . '/' . $entry;
                    if ($entry != '.' && $entry != '..' && is_file($fullPath)) {
                        $content = file_get_contents($fullPath);
                        if ($content !== false) {
                            $document = new $this->documentClassName();
                            $document->content = $content;
                            $document->sourceType = $this->sourceType;
                            $document->sourceName = $entry;
                            yield $document;
                        }
                    }
                }

                closedir($handle);
            }
        }
    }
}
