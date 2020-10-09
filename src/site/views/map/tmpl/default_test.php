<?php

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

echo '<h3>' . basename(__FILE__) . '</h3>';

$texts = [
    'COM_FOCALPOINT_BUTTTON_HIDE_ALL',
    'COM_FOCALPOINT_BUTTTON_SHOW_ALL',
    'COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED',
    'COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED',
    'COM_FOCALPOINT_SEARCH_SHOWING',
    'COM_FOCALPOINT_SEARCH_SHOWING_1',
    'COM_FOCALPOINT_SEARCH_WITHIN',
    'COM_FOCALPOINT_SEARCH_WITHIN_1'
];
foreach ($texts as $text) {
    Text::script($text);
}

$markers = [];
foreach ($this->item->markerdata as $marker) {
    //Assemble the infobox.
    $infoDescription = [];
    if ($marker->params->get('infoshowaddress') && $marker->address != '') {
        $infoDescription[] = '<p>' . Text::_($marker->address) . '</p>';
    }
    if ($marker->params->get('infoshowphone') && $marker->phone != '') {
        $infoDescription[] = '<p>' . Text::_($marker->phone) . '</p>';
    }
    if ($marker->params->get('infoshowintro') && $marker->description != '') {
        $infoDescription[] = '<p>' . Text::_($marker->description) . '</p>';
    }

    // Example. If a custom fields was defined called 'yourcustomfield' the following line would render
    // that field in the infobox and location list
    if (!empty($marker->customfields->yourcustomfield->data)) {
        $infoDescription[] = $this->renderField($marker->customfields->yourcustomfield, true, true);
    }

    $boxText = sprintf(
        '<h4>%s</h4><div class="infoboxcontent">%s',
        $marker->title,
        join('', $infoDescription)
    );
    if (preg_match_all('/<img.*?src="(image[^"].*?)".*?>/', $boxText, $images)) {
        $fixed = [];
        foreach ($images[0] as $idx => $source) {
            $imageUri    = HTMLHelper::_('image', $images[1][$idx], null, null, false, 1);
            $fixed[$idx] = str_replace($images[1][$idx], $imageUri, $source);
        }
        $boxText = str_replace($images[0], $fixed, $boxText);
    }

    if (isset($marker->link)) {
        $boxText .= sprintf(
            '<p class="infoboxlink">%s</p>',
            HTMLHelper::_(
                'link',
                $marker->link,
                Text::_('COM_FOCALPOINT_FIND_OUT_MORE'),
                ['title' => $marker->title]
            )
        );
    }
    $boxText .= '<div class="infopointer"></div></div>';

    $boxText = addslashes(str_replace(["\n", "\t", "\r"], '', $boxText));

    $markers[] = [
        'id'       => (int)$marker->id,
        'typeId'   => (int)$marker->locationtype_id,
        'infoBox'  => [
            'content' => $boxText,
        ],
        'marker'   => $marker->marker,
        'position' => [
            'lat' => $marker->latitude,
            'lng' => $marker->longitude
        ]
    ];
}

HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?key=' . $this->item->params->get('apikey'));
HTMLHelper::_('script', 'components/com_focalpoint/assets/js/infobox.js');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'com_focalpoint/googleMap.js', ['relative' => true]);

$params          = ComponentHelper::getParams('com_focalpoint');
$mapsearchprompt = $this->item->params->get('mapsearchprompt');
$listtabfirst    = (int)(bool)$this->item->params->get('showlistfirst');

$options = json_encode([
    'clusterOptions' => $this->item->params->get('clusterOptions', null),
    'fitBounds'      => (bool)$this->item->params->get('fitbounds'),
    'mapProperties'  => [
        'center'                   => [
            'lat' => $this->item->latitude,
            'lng' => $this->item->longitude
        ],
        'draggable'                => (int)(bool)$this->item->params->get('draggable'),
        'fullscreenControl'        => (int)(bool)$this->item->params->get('fullscreen'),
        'fullscreenControlOptions' => $this->item->params->get('fullscreenOptions', null),
        'mapTypeControl'           => (int)$this->item->params->get('mapTypeControl'),
        'mapTypeId'                => $this->item->params->get('mapTypeId'),
        'maxZoom'                  => $this->item->params->get('maxzoom', null),
        'panControl'               => (int)(bool)$this->item->params->get('panControl'),
        'scrollwheel'              => (int)(bool)$this->item->params->get('scrollwheel'),
        'streetViewControl'        => (int)(bool)$this->item->params->get('streetViewControl'),
        'styles'                   => $this->item->params->get('mapstyle', []),
        'zoom'                     => (int)$this->item->params->get('zoom'),
        'zoomControl'              => (int)(bool)$this->item->params->get('zoomControl'),
    ],
    'markerData'     => $markers,
    'search'         => [
        'assist' => (string)$this->item->params->get('searchassist', ''),
        'radius' => (float)$this->item->params->get('resultradius', 15),
        'zoom'   => (int)$this->item->params->get('mapsearchzoom', 12)
    ],
    'show'           => [
        'clusters' => (bool)$this->item->params->get('markerclusters'),
        'listTab'  => (bool)$this->item->params->get('locationlist'),
        'markers'  => $this->item->params->get('showmarkers') == 'on',
        'search'   => (bool)$this->item->params->get('mapsearchenabled')
    ]
]);

$init = <<<JSINIT
jQuery(document).ready(function ($) {
    (new $.sloc.map.google).init({$options});
});
JSINIT;
Factory::getDocument()->addScriptDeclaration($init);
