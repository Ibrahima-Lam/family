<?php

namespace App\Support;

use RuntimeException;

/** Builds portable, single-file editions of the family book and tree. */
final class InlineFamilyPagesGenerator
{
    private string $outputDir;
    private ?string $rootId;

    public function __construct(string $outputDir, ?string $rootId = null)
    {
        $this->outputDir = rtrim($outputDir, '/');
        $this->rootId = $rootId;
    }

    /** @return list<string> Absolute paths of generated HTML files. */
    public function generate(): array
    {
        $projectRoot = dirname(__DIR__, 2);
        $tempDir = sys_get_temp_dir() . '/family-inline-' . bin2hex(random_bytes(8));

        if (!mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
            throw new RuntimeException('Impossible de créer le dossier temporaire d’export.');
        }

        try {
            (new StaticSiteGenerator($tempDir, $this->rootId))->generate();
            $this->ensureOutputDir();

            $files = [
                'famille-livre.html' => 'livre.html',
                'famille-arbre.html' => 'arbre.html',
            ];
            $written = [];

            foreach ($files as $outputName => $sourceName) {
                $sourcePath = $tempDir . '/' . $sourceName;
                $html = file_get_contents($sourcePath);
                if ($html === false) {
                    throw new RuntimeException('Impossible de lire la page générée : ' . $sourceName);
                }

                $html = $this->inlineAssets($html, $tempDir);
                $html = str_replace('href="index.html"', 'href="famille-livre.html"', $html);
                $html = str_replace('href="liste.html"', 'href="famille-livre.html"', $html);
                $html = str_replace('href="livre.html"', 'href="famille-livre.html"', $html);
                $html = str_replace('href="arbre.html"', 'href="famille-arbre.html"', $html);
                $html = preg_replace(
                    '~href="personne/([^"/]+)\.html"~',
                    'href="famille-livre.html#$1"',
                    $html
                ) ?? $html;

                $targetPath = $this->outputDir . '/' . $outputName;
                if (file_put_contents($targetPath, $html) === false) {
                    throw new RuntimeException('Impossible d’écrire le fichier : ' . $targetPath);
                }
                $written[] = $targetPath;
            }

            return $written;
        } finally {
            $this->removeDirectory($tempDir);
        }
    }

    private function inlineAssets(string $html, string $siteDir): string
    {
        $cssPath = $siteDir . '/assets/css/modern.css';
        $css = file_get_contents($cssPath);
        if ($css === false) {
            throw new RuntimeException('Impossible de lire la feuille de style de l’export.');
        }
        $html = preg_replace(
            '~<link\s+rel="stylesheet"\s+href="[^"]*modern\.css"\s*>~i',
            '<style>' . "\n" . $css . "\n" . '</style>',
            $html
        ) ?? $html;

        return preg_replace_callback(
            '~<script\s+src="([^"]+)"\s+defer></script>~i',
            static function (array $match) use ($siteDir): string {
                $relative = preg_replace('~^.*?assets/~', 'assets/', $match[1]);
                $path = $siteDir . '/' . $relative;
                if (!is_file($path)) {
                    return '';
                }
                $javascript = file_get_contents($path);
                return $javascript === false ? '' : '<script>' . "\n" . $javascript . "\n" . '</script>';
            },
            $html
        ) ?? $html;
    }

    private function ensureOutputDir(): void
    {
        if (!is_dir($this->outputDir) && !mkdir($this->outputDir, 0775, true) && !is_dir($this->outputDir)) {
            throw new RuntimeException('Impossible de créer le dossier de sortie : ' . $this->outputDir);
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }
}
