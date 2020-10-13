<?php

use Joomla\CMS\HTML\HTMLHelper;

$center = [
    'lat' => $this->item->latitude,
    'lng' => $this->item->longitude
];

HTMLHelper::_('slocgoogle.map', $this->item->id, $this->item->params, $center, $this->item->markerdata);

