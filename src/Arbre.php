<?php

namespace Src;

use App\Models\Personne;

class Arbre
{
    private Personne $model;
    /** @var array<string, array<string, mixed>> */
    private array $byId = [];
    /** @var array<string, list<string>> */
    private array $childrenByParent = [];
    /** @var array<string, bool> */
    private array $visited = [];
    private ?string $rootId = null;
    private string $detailsPrefix = '?p=personne/details/';
    private string $detailsSuffix = '';

    public function __construct($id = null)
    {
        $this->model = new Personne();
        $this->loadData();
        $this->rootId = $this->resolveRoot($id);
    }

    public function setDetailsLink(string $prefix, string $suffix = ''): void
    {
        $this->detailsPrefix = $prefix;
        $this->detailsSuffix = $suffix;
    }

    private function loadData(): void
    {
        $rows = $this->model->getAll();

        foreach ($rows ?: [] as $row) {
            $personId = (string) ($row['id'] ?? '');
            if ($personId === '') {
                continue;
            }

            $this->byId[$personId] = $row;
            $parentId = (string) ($row['idParent'] ?? '');

            if ($parentId !== '') {
                $this->childrenByParent[$parentId][] = $personId;
            }
        }
    }

    private function resolveRoot($id): ?string
    {
        $candidate = $id === null ? '' : (string) $id;

        if ($candidate !== '' && isset($this->byId[$candidate])) {
            return $candidate;
        }

        if ($candidate !== '') {
            foreach ($this->byId as $personId => $row) {
                if ((string) ($row['generation'] ?? '') === $candidate) {
                    return (string) $personId;
                }
            }
        }

        foreach ($this->byId as $personId => $row) {
            if (empty($row['idParent'])) {
                return (string) $personId;
            }
        }

        $ids = array_keys($this->byId);

        return $ids === [] ? null : (string) $ids[0];
    }

    public function getRootId(): ?string
    {
        return $this->rootId;
    }

    public function getRootName(): string
    {
        if ($this->rootId === null) {
            return '';
        }

        return (string) ($this->byId[$this->rootId]['nom'] ?? '');
    }

    public function getRootSurname(): string
    {
        if ($this->rootId === null) {
            return '';
        }

        return (string) ($this->byId[$this->rootId]['surnom'] ?? '');
    }

    public function getResult(): string
    {
        if ($this->rootId === null) {
            return '<p class="empty">Aucune personne dans l’arbre.</p>';
        }

        $this->visited = [];

        return '<dl class="tree-root">' . $this->renderNode($this->rootId, true) . '</dl>';
    }

    public function getLineages(string $linkPrefix = '?p=personne/details/', string $linkSuffix = ''): string
    {
        $html = '';

        foreach (array_keys($this->byId) as $personId) {
            $html .= $this->getLineage((string) $personId, $linkPrefix, $linkSuffix);
        }

        return $html;
    }

    public function getLineage(string $id, string $linkPrefix = '?p=personne/details/', string $linkSuffix = ''): string
    {
        if (!isset($this->byId[$id])) {
            return '';
        }

        return $this->renderLineage($id, $linkPrefix, $linkSuffix);
    }

    public function makeChildren($id = null): string
    {
        $personId = $id === null ? $this->rootId : (string) $id;
        if ($personId === null || !isset($this->byId[$personId])) {
            return '';
        }

        $this->visited = [];
        $html = '';

        foreach ($this->childrenByParent[$personId] ?? [] as $childId) {
            $html .= $this->renderNode((string) $childId, false);
        }

        return $html;
    }

    private function renderNode(string $id, bool $open): string
    {
        if (isset($this->visited[$id]) || !isset($this->byId[$id])) {
            return '';
        }
        $this->visited[$id] = true;

        $row = $this->byId[$id];
        $children = $this->childrenByParent[$id] ?? [];
        $count = count($children);
        $isOpen = $open && $count > 0;
        $statut = descendance_status($row['enfantsStatut'] ?? 'inconnu');
        $manquants = (int) ($row['enfantsManquants'] ?? 0);
        $attention = descendance_needs_attention($statut, $manquants);
        $inconnu = $statut === 'inconnu';
        $badge = $attention
            ? '<span class="tree-attention badge badge-warning" title="' . e(descendance_description($statut)) . '">'
                . ($manquants > 0 ? e($manquants) . ' manquant' . ($manquants > 1 ? 's' : '') : 'À vérifier')
                . '</span>'
            : '';

        $html = '<dd class="tree-node" data-generation="' . e((string) ($row['generation'] ?? '')) . '">';
        $html .= '<details id="node-' . e($id) . '"' . ($isOpen ? ' open' : '') . '>';
        $html .= '<summary>';
        $html .= '<span class="tree-name"><a href="' . e($this->detailsPrefix) . e($id) . e($this->detailsSuffix) . '">'
            . person_name($row['nom'] ?? '', $row['surnom'] ?? null) . '</a></span>';
        $html .= '<span class="tree-id">(' . e($id) . ')</span>';
        $html .= $badge;
        $html .= '<span class="tree-count' . ($count === 0 ? ' tree-count-empty' : '')
            . ($inconnu ? ' tree-count-unknown' : '')
            . ($attention ? ' tree-count-attention' : '') . '" title="'
            . e($count . ' enfant' . ($count > 1 ? 's' : '') . ' · ' . descendance_label($statut))
            . '">' . $count . '</span>';
        $html .= '</summary>';

        if ($children !== []) {
            $firstChild = $this->byId[$children[0]] ?? [];
            $generation = $this->generationLabel($firstChild);

            $html .= '<dl class="' . e($generation) . '">';
            $html .= '<dt>' . e($generation) . '</dt>';

            foreach ($children as $childId) {
                $html .= $this->renderNode((string) $childId, false);
            }

            $html .= '</dl>';
        }

        $html .= '</details></dd>';

        return $html;
    }

    private function renderLineage(string $id, string $linkPrefix, string $linkSuffix = ''): string
    {
        $chain = [];
        $seen = [];
        $current = $id;

        while (isset($this->byId[$current])) {
            if (isset($seen[$current])) {
                break;
            }
            $seen[$current] = true;

            $parentId = (string) ($this->byId[$current]['idParent'] ?? '');
            if ($parentId === '' || !isset($this->byId[$parentId])) {
                break;
            }

            $chain[] = $parentId;
            $current = $parentId;
        }

        $chain = array_reverse($chain);
        if ($chain === []) {
            return '';
        }

        $html = '<article class="lineage" id="lineage-' . e($id) . '">';
        $html .= '<p class="lineage-chain">';

        foreach ($chain as $ancestorId) {
            $ancestor = $this->byId[$ancestorId];
            $html .= '<a href="' . e($linkPrefix) . e($ancestorId) . e($linkSuffix) . '">'
                . person_name($ancestor['nom'] ?? '', $ancestor['surnom'] ?? null) . '</a>';
            $html .= '<span class="lineage-arrow" aria-hidden="true">→</span>';
        }

        $person = $this->byId[$id];
        $html .= '<strong>' . person_name($person['nom'] ?? '', $person['surnom'] ?? null) . '</strong>';
        $html .= '<span class="lineage-id">(' . e($id) . ')</span>';
        $html .= '</p></article>';

        return $html;
    }

    /** @param array<string, mixed> $row */
    private function generationLabel(array $row): string
    {
        $generation = (int) ($row['generation'] ?? 0);

        return $generation > 0 ? 'G' . $generation : (string) ($row['nomGeneration'] ?? '');
    }
}
