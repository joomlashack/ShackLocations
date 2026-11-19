<?php

/**
 * Stands in for the real map templates, which need a full site template to render
 */

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

echo 'Rendered: ' . $this->item->title;
