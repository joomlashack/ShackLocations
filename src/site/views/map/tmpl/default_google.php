<?php

use Joomla\CMS\HTML\HTMLHelper;

$center = [
    'lat' => $this->item->latitude,
    'lng' => $this->item->longitude
];

$params = clone $this->item->params;
$params->set('showlegend', true);

HTMLHelper::_('slocgoogle.map', $this->item->id, $params, $center, $this->item->markerdata);

