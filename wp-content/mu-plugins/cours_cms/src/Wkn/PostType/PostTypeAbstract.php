<?php

namespace Wkn\PostType;

defined('ABSPATH') || exit;

/**
 * Classe abstraite pour les Custom Post Types.
 */
abstract class PostTypeAbstract
{
    /** @var string Slug technique du CPT */
    protected $_name = '';

    /** @var array Labels d'admin */
    protected $_labels = [];

    /** @var array Arguments register_post_type */
    protected $_args = [];

    public function __construct()
    {
        // Les classes filles définissent _name, _labels, _args
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

    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Enregistre le CPT.
     */
    public function register(): void
    {
        $args = array_merge([
            'labels' => $this->_labels,
            'label'  => $this->_labels['name'] ?? $this->_name,
        ], $this->_args);
        register_post_type($this->_name, $args);
    }
}
