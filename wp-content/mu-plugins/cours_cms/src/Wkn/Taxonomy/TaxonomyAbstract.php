<?php

namespace Wkn\Taxonomy;

defined('ABSPATH') || exit;

/**
 * Classe abstraite pour les taxonomies.
 */
abstract class TaxonomyAbstract
{
    /** @var string Slug technique de la taxonomie */
    protected $_name = '';

    /** @var array Labels d'admin */
    protected $_labels = [];

    /** @var array Arguments register_taxonomy */
    protected $_args = [];

    /** @var array Post types associés */
    protected $_post_types = [];

    public function __construct()
    {
    }

    /**
     * Définit les labels (fluent).
     * @param array<string, string> $labels
     */
    public function setLabels(array $labels): self
    {
        $this->_labels = $labels;
        return $this;
    }

    /**
     * Définit les arguments (fluent).
     * @param array<string, mixed> $args
     */
    public function setArgs(array $args): self
    {
        $this->_args = $args;
        return $this;
    }

    /**
     * Associe la taxonomie à des CPT (fluent).
     * @param array<int, string|object> $postTypes Noms de CPT ou instances avec getName()
     */
    public function setPostTypes(array $postTypes): self
    {
        $this->_post_types = $postTypes;
        return $this;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Enregistre la taxonomie.
     */
    public function register(): void
    {
        $postTypes = [];
        foreach ($this->_post_types as $pt) {
            $postTypes[] = is_object($pt) && method_exists($pt, 'getName') ? $pt->getName() : (string) $pt;
        }
        if (empty($postTypes)) {
            return;
        }
        $args = array_merge([
            'labels' => $this->_labels,
            'label'  => $this->_labels['name'] ?? $this->_name,
        ], $this->_args);
        register_taxonomy($this->_name, $postTypes, $args);
    }
}
