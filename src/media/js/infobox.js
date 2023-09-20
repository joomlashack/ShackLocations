/**
 * @name         InfoBox
 * @version      1.1.13 [March 19, 2014]
 * @author       Gary Little (inspired by proof-of-concept code from Pamela Fox of Google)
 * @copyright    Copyright 2010 Gary Little [gary at luxcentral.com]
 * @copyright    Copyright 2021 joomlashack.com
 * @fileoverview InfoBox extends the Google Maps JavaScript API V3 <tt>OverlayView</tt> class.
 *  <p>
 *  An InfoBox behaves like a <tt>google.maps.InfoWindow</tt>, but it supports several
 *  additional properties for advanced styling. An InfoBox can also be used as a map label.
 *  <p>
 *  An InfoBox also fires the same events as a <tt>google.maps.InfoWindow</tt>.
 */

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @name     InfoBoxOptions
 * @class    This class represents the optional parameter passed to the {@link InfoBox} constructor.
 *
 * @property {string|Node} content                The content of the InfoBox (plain text or an HTML DOM node).
 * @property {boolean}     [disableAutoPan=false] Disable auto-pan on <tt>open</tt>.
 * @property {number}      maxWidth               The maximum width (in pixels) of the InfoBox. Set to 0 if no maximum.
 * @property {Size}        pixelOffset            The offset (in pixels) from the top left corner of the InfoBox
 *                                                (or the bottom left corner if the <code>alignBottom</code> property
 *                                                is <code>true</code>)
 *                                                to the map pixel corresponding to <tt>position</tt>.
 * @property {LatLng}      position               The geographic location at which to display the InfoBox.
 * @property {number}      zIndex                 The CSS z-index style value for the InfoBox.
 *                                                Note: This value overrides a zIndex setting specified in the
 *                                                <tt>boxStyle</tt> property.
 * @property {string}      [boxClass='infoBox']   The name of the CSS class defining the styles for the InfoBox container.
 * @property {Object}      [boxStyle]             An object literal whose properties define specific CSS style values
 *                                                to be applied to the InfoBox. Style values defined here override
 *                                                those that may be defined in the <code>boxClass</code> style sheet.
 *                                                If this property is changed after the InfoBox has been created, all
 *                                                previously set styles (except those defined in the style sheet) are
 *                                                removed from the InfoBox before the new style values are applied.
 * @property {string}      closeBoxMargin         The CSS margin style value for the close box. The default is '2px'
 *                                                (a 2-pixel margin on all sides).
 * @property {string}      closeBoxURL            The URL of the image representing the close box.
 *                                                Note: The default is the URL for Google's standard close box.
 *                                                Set this property to '' if no close box is required.
 * @property {Size}        infoBoxClearance       Minimum offset (in pixels) from the InfoBox to the map edge after
 *                                                an auto-pan.
 * @property {boolean}     [isHidden=false]       Hide the InfoBox on <tt>open</tt>.
 *                                                [Deprecated in favor of the <tt>visible</tt> property.]
 * @property {boolean}     [visible=true]         Show the InfoBox on <tt>open</tt>.
 * @property {boolean}     alignBottom            Align the bottom left corner of the InfoBox to the
 *                                                <code>position</code> location (default is <tt>false</tt>
 *                                                which means that the top left corner of the InfoBox is aligned).
 * @property {string}      pane                   The pane where the InfoBox is to appear (default is 'floatPane').
 *                                                Set the pane to 'mapPane' if the InfoBox is being used as a map label.
 *                                                Valid pane names are the property names for the
 *                                                <tt>google.maps.MapPanes</tt> object.
 * @property {boolean}     enableEventPropagation Propagate mousedown, mousemove, mouseover, mouseout, mouseup, click,
 *                                                dblclick, touchstart, touchend, touchmove, and contextmenu events in
 *                                                the InfoBox (default is <tt>false</tt> to mimic the behavior of a
 *                                                <tt>google.maps.InfoWindow</tt>). Set this property to <tt>true</tt>
 *                                                if the InfoBox is being used as a map label.
 */

/**
 * Creates an InfoBox with the options specified in {@link InfoBoxOptions}.
 *  Call <tt>InfoBox.open</tt> to add the box to the map.
 *
 * @param {Object} [options]
 */
function InfoBox(options) {
    options = options || {};

    google.maps.OverlayView.apply(this, arguments);

    // Standard options (in common with google.maps.InfoWindow):
    this.content_        = options.content || '';
    this.disableAutoPan_ = options.disableAutoPan || false;
    this.maxWidth_       = options.maxWidth || 0;
    this.pixelOffset_    = options.pixelOffset || new google.maps.Size(0, 0);
    this.position_       = options.position || new google.maps.LatLng(0, 0);
    this.zIndex_         = options.zIndex || null;

    // Additional options (unique to InfoBox):
    this.boxClass_       = options.boxClass || 'infoBox';
    this.boxStyle_       = options.boxStyle || {};
    this.closeBoxMargin_ = options.closeBoxMargin || '2px';
    this.closeBoxURL_    = options.closeBoxURL || 'https://www.google.com/intl/en_us/mapfiles/close.gif';
    if (options.closeBoxURL === '') {
        this.closeBoxURL_ = '';
    }
    this.infoBoxClearance_ = options.infoBoxClearance || new google.maps.Size(1, 1);

    if (typeof options.visible === 'undefined') {
        if (typeof options.isHidden === 'undefined') {
            options.visible = true;
        } else {
            options.visible = !options.isHidden;
        }
    }
    this.isHidden_ = !options.visible;

    this.alignBottom_            = options.alignBottom || false;
    this.pane_                   = options.pane || 'floatPane';
    this.enableEventPropagation_ = options.enableEventPropagation || false;

    this.div_           = null;
    this.moveListener_  = null;
    this.fixedWidthSet_ = null;

    this.domEventListeners_ = [];
    this.cancelEvents       = [
        'mousedown',
        'mouseover',
        'mouseout',
        'mouseup',
        'click',
        'dblclick',
        'touchstart',
        'touchend',
        'touchmove'
    ];
}

/* InfoBox extends OverlayView in the Google Maps API v3.
 */
InfoBox.prototype = new google.maps.OverlayView();

/**
 * Creates the <div> representing the InfoBox.
 */
InfoBox.prototype.createInfoBoxDiv_ = function() {
    let bw,
        me = this;

    /**
     *  Prevent an event in the InfoBox from being passed on to the map.
     *
     * @param {Event} event
     *
     * @return void
     */
    let cancelHandler = function(event) {
        event.cancelBubble = true;
        if (event.stopPropagation) {
            event.stopPropagation();
        }
    };

    /**
     * Ignore the current event in the InfoBox and conditionally prevent
     * the event from being passed on to the map. It is used for the contextmenu event.
     *
     * @param {Event} event
     *
     * @return void
     */
    let ignoreHandler = function(event) {
        event.preventDefault();

        if (!me.enableEventPropagation_) {
            cancelHandler(event);
        }
    };

    if (!this.div_) {
        this.div_ = document.createElement('div');
        this.clearDomEventListeners();

        this.setBoxStyle_();

        if (typeof this.content_.nodeType === 'undefined') {
            this.div_.innerHTML = this.content_;
        } else {
            this.div_.appendChild(this.content_);
        }
        this.div_.prepend(this.getCloseBoxImg_());

        // Add the InfoBox DIV to the DOM
        this.getPanes()[this.pane_].appendChild(this.div_);

        this.addCloseBox_();

        if (this.div_.style.width) {
            this.fixedWidthSet_ = true;

        } else {
            if (this.maxWidth_ !== 0 && this.div_.offsetWidth > this.maxWidth_) {
                this.div_.style.width    = this.maxWidth_;
                this.div_.style.overflow = 'auto';
                this.fixedWidthSet_      = true;

            } else {
                // The following code is needed to overcome problems with MSIE
                bw = this.getBoxWidths_();

                this.div_.style.width = (this.div_.offsetWidth - bw.left - bw.right) + 'px';
                this.fixedWidthSet_   = false;
            }
        }

        this.panBox_(this.disableAutoPan_);

        if (!this.enableEventPropagation_) {
            this.cancelEvents.forEach(function(event) {
                me.addDomEventListener(event, cancelHandler);
            });

            /*
             * Workaround for Google bug that causes the cursor to change to a pointer
             * when the mouse moves over a marker underneath InfoBox.
             */
            this.addDomEventListener('mouseover', function() {
                this.style.cursor = 'default';
            });
        }

        this.addDomEventListener('contextmenu', ignoreHandler);

        /**
         * This event is fired when the DIV containing the InfoBox's content is attached to the DOM.
         * @name InfoBox#domready
         */
        google.maps.event.trigger(this, 'domready');
    }
};

/**
 * Returns the HTML <img> element for the close box.
 *
 * @return {Image|String}
 */
InfoBox.prototype.getCloseBoxImg_ = function() {
    let img = '';

    if (this.closeBoxURL_ !== '') {
        img = document.createElement('img');
        img.setAttribute('src', this.closeBoxURL_);
        img.style.cursor = 'pointer';
        img.style.margin = this.closeBoxMargin_;
        img.style.float  = 'right';

    }

    return img;
};

/**
 * create and save event listeners for later removal
 *
 * @param {String}   event
 * @param {Function} handler
 * @param {Element}  [element]
 *
 * @return void
 */
InfoBox.prototype.addDomEventListener = function(event, handler, element) {
    let item = {
        element: element || this.div_,
        event  : event,
        handler: handler

    }

    this.domEventListeners_.push(item);
    item.element.addEventListener(item.event, item.handler);
};

/**
 * Clear all the saved event listeners
 *
 * @return void
 */
InfoBox.prototype.clearDomEventListeners = function() {
    this.domEventListeners_.forEach(function(item) {
        item.element.removeEventListener(item.event, item.handler);
    });
    this.domEventListeners_ = [];
};

/**
 * Adds the click handler to the InfoBox close box.
 * @private
 */
InfoBox.prototype.addCloseBox_ = function() {
    if (this.closeBoxURL_ !== '') {
        this.addDomEventListener('click', this.closeHandler, this.div_.firstChild);
    }
};

/**
 * Returns the function to call when the user clicks the close box of an InfoBox.
 *
 * @param {Event} event
 */
InfoBox.prototype.closeHandler = function(event) {
    // 1.0.3 fix: Always prevent propagation of a close box click to the map:
    event.cancelBubble = true;

    event.stopPropagation();

    google.maps.event.trigger(this, 'closeclick');

    this.close();
};

/**
 * Pans the map so that the InfoBox appears entirely within the map's visible area.
 *
 * @param {Boolean} disablePan
 *
 * @return void
 */
InfoBox.prototype.panBox_ = function(disablePan) {
    let map,
        xOffset = 0,
        yOffset = 0;

    if (!disablePan) {
        map = this.getMap();

        if (map instanceof google.maps.Map) {
            // Only pan if attached to map, not panorama
            if (!map.getBounds().contains(this.position_)) {
                /*
                 * Marker not in visible area of map, so set center
                 * of map to the marker position first.
                 */
                map.setCenter(this.position_);
            }

            let mapDiv      = map.getDiv(),
                mapWidth    = mapDiv.offsetWidth,
                mapHeight   = mapDiv.offsetHeight,
                iwOffsetX   = this.pixelOffset_.width,
                iwOffsetY   = this.pixelOffset_.height,
                iwWidth     = this.div_.offsetWidth,
                iwHeight    = this.div_.offsetHeight,
                padX        = this.infoBoxClearance_.width,
                padY        = this.infoBoxClearance_.height,
                pixPosition = this.getProjection().fromLatLngToContainerPixel(this.position_);

            if (pixPosition.x < (-iwOffsetX + padX)) {
                xOffset = pixPosition.x + iwOffsetX - padX;

            } else if ((pixPosition.x + iwWidth + iwOffsetX + padX) > mapWidth) {
                xOffset = pixPosition.x + iwWidth + iwOffsetX + padX - mapWidth;
            }

            if (this.alignBottom_) {
                if (pixPosition.y < (-iwOffsetY + padY + iwHeight)) {
                    yOffset = pixPosition.y + iwOffsetY - padY - iwHeight;

                } else if ((pixPosition.y + iwOffsetY + padY) > mapHeight) {
                    yOffset = pixPosition.y + iwOffsetY + padY - mapHeight;
                }

            } else {
                if (pixPosition.y < (-iwOffsetY + padY)) {
                    yOffset = pixPosition.y + iwOffsetY - padY;

                } else if ((pixPosition.y + iwHeight + iwOffsetY + padY) > mapHeight) {
                    yOffset = pixPosition.y + iwHeight + iwOffsetY + padY - mapHeight;
                }
            }

            if (xOffset || yOffset) {
                // Move the map to the shifted center.
                map.panBy(xOffset, yOffset);
            }
        }
    }
};

/**
 * Sets the style of the InfoBox by setting the style sheet and applying
 * other specific styles requested.
 *
 * @return void
 */
InfoBox.prototype.setBoxStyle_ = function() {
    let i,
        boxStyle;

    if (this.div_) {
        // Apply style values from the style sheet defined in the boxClass parameter:
        this.div_.className = this.boxClass_;

        // Clear existing inline style values:
        this.div_.style.cssText = '';

        // Apply style values defined in the boxStyle parameter:
        boxStyle = this.boxStyle_;
        for (i in boxStyle) {
            if (boxStyle.hasOwnProperty(i)) {
                this.div_.style[i] = boxStyle[i];
            }
        }

        /*
         * Fix for iOS disappearing InfoBox problem.
         * See http://stackoverflow.com/questions/9229535/google-maps-markers-disappear-at-certain-zoom-level-only-on-iphone-ipad
         */
        this.div_.style.WebkitTransform = 'translateZ(0)';

        // Fix up opacity style for benefit of MSIE:
        if (typeof this.div_.style.opacity !== 'undefined' && this.div_.style.opacity !== '') {
            // See http://www.quirksmode.org/css/opacity.html
            this.div_.style.MsFilter = '"progid:DXImageTransform.Microsoft.Alpha(Opacity=' + (this.div_.style.opacity * 100) + ')"';
            this.div_.style.filter   = 'alpha(opacity=' + (this.div_.style.opacity * 100) + ')';
        }

        // Apply required styles:
        this.div_.style.position   = 'absolute';
        this.div_.style.visibility = 'hidden';
        if (this.zIndex_ !== null) {
            this.div_.style.zIndex = this.zIndex_;
        }
    }
};

/**
 * Get the widths of the borders of the InfoBox.
 *
 * @return {Object} widths object (top, bottom left, right)
 */
InfoBox.prototype.getBoxWidths_ = function() {
    let computedStyle,
        bw  = {top: 0, bottom: 0, left: 0, right: 0},
        box = this.div_;

    if (document.defaultView && document.defaultView.getComputedStyle) {
        computedStyle = box.ownerDocument.defaultView.getComputedStyle(box, '');

        if (computedStyle) {
            // The computed styles are always in pixel units (good!)
            bw.top    = parseInt(computedStyle.borderTopWidth, 10) || 0;
            bw.bottom = parseInt(computedStyle.borderBottomWidth, 10) || 0;
            bw.left   = parseInt(computedStyle.borderLeftWidth, 10) || 0;
            bw.right  = parseInt(computedStyle.borderRightWidth, 10) || 0;
        }

    }

    return bw;
};

/**
 * Invoked when <tt>close</tt> is called. Do not call it directly.
 */
InfoBox.prototype.onRemove = function() {
    if (this.div_) {
        this.div_.parentNode.removeChild(this.div_);
        this.div_ = null;
    }
};

/**
 * Draws the InfoBox based on the current map projection and zoom level.
 */
InfoBox.prototype.draw = function() {
    this.createInfoBoxDiv_();

    let pixPosition = this.getProjection().fromLatLngToDivPixel(this.position_);

    this.div_.style.left = (pixPosition.x + this.pixelOffset_.width) + 'px';

    if (this.alignBottom_) {
        this.div_.style.bottom = -(pixPosition.y + this.pixelOffset_.height) + 'px';

    } else {
        this.div_.style.top = (pixPosition.y + this.pixelOffset_.height) + 'px';
    }

    if (this.isHidden_) {
        this.div_.style.visibility = 'hidden';

    } else {
        this.div_.style.visibility = 'visible';
    }
};

/**
 * Sets the options for the InfoBox. Note that changes to the <tt>maxWidth</tt>,
 *  <tt>closeBoxMargin</tt>, <tt>closeBoxURL</tt>, and <tt>enableEventPropagation</tt>
 *  properties have no affect until the current InfoBox is <tt>close</tt>d and a new one
 *  is <tt>open</tt>ed.
 *
 * @param {Object} options
 */
InfoBox.prototype.setOptions = function(options) {
    if (typeof options.boxClass !== 'undefined') { // Must be first
        this.boxClass_ = options.boxClass;
        this.setBoxStyle_();
    }

    if (typeof options.boxStyle !== 'undefined') { // Must be second
        this.boxStyle_ = options.boxStyle;
        this.setBoxStyle_();
    }

    if (typeof options.content !== 'undefined') {
        this.setContent(options.content);
    }

    if (typeof options.disableAutoPan !== 'undefined') {
        this.disableAutoPan_ = options.disableAutoPan;
    }

    if (typeof options.maxWidth !== 'undefined') {
        this.maxWidth_ = options.maxWidth;
    }

    if (typeof options.pixelOffset !== 'undefined') {
        this.pixelOffset_ = options.pixelOffset;
    }

    if (typeof options.alignBottom !== 'undefined') {
        this.alignBottom_ = options.alignBottom;
    }

    if (typeof options.position !== 'undefined') {
        this.setPosition(options.position);
    }

    if (typeof options.zIndex !== 'undefined') {
        this.setZIndex(options.zIndex);
    }

    if (typeof options.closeBoxMargin !== 'undefined') {
        this.closeBoxMargin_ = options.closeBoxMargin;
    }

    if (typeof options.closeBoxURL !== 'undefined') {
        this.closeBoxURL_ = options.closeBoxURL;
    }

    if (typeof options.infoBoxClearance !== 'undefined') {
        this.infoBoxClearance_ = options.infoBoxClearance;
    }

    if (typeof options.isHidden !== 'undefined') {
        this.isHidden_ = options.isHidden;
    }

    if (typeof options.visible !== 'undefined') {
        this.isHidden_ = !options.visible;
    }

    if (typeof options.enableEventPropagation !== 'undefined') {
        this.enableEventPropagation_ = options.enableEventPropagation;
    }

    if (this.div_) {
        this.draw();
    }
};

/**
 * Sets the content of the InfoBox.
 *  The content can be plain text or an HTML DOM node.
 *
 * @param {string|Node} content
 */
InfoBox.prototype.setContent = function(content) {
    this.content_ = content;

    if (this.div_) {
        if (typeof content.nodeType === 'undefined') {
            this.div_.innerHTML = content;

        } else {
            this.div_.appendChild(content);
        }
        this.div_.prepend(this.getCloseBoxImg_());

        this.addCloseBox_();
    }

    /**
     * This event is fired when the content of the InfoBox changes.
     * @name InfoBox#content_changed
     */
    google.maps.event.trigger(this, 'content_changed');
};

/**
 * Sets the geographic location of the InfoBox.
 *
 * @param {LatLng} latlng
 */
InfoBox.prototype.setPosition = function(latlng) {
    this.position_ = latlng;

    if (this.div_) {
        this.draw();
    }

    /**
     * This event is fired when the position of the InfoBox changes.
     * @name InfoBox#position_changed
     */
    google.maps.event.trigger(this, 'position_changed');
};

/**
 * Sets the zIndex style for the InfoBox.
 *
 * @param {Number} index
 */
InfoBox.prototype.setZIndex = function(index) {
    this.zIndex_ = index;

    if (this.div_) {
        this.div_.style.zIndex = index;
    }

    /**
     * This event is fired when the zIndex of the InfoBox changes.
     * @name InfoBox#zindex_changed
     */
    google.maps.event.trigger(this, 'zindex_changed');
};

/**
 * Sets the visibility of the InfoBox.
 *
 * @param {Boolean} isVisible
 */
InfoBox.prototype.setVisible = function(isVisible) {
    this.isHidden_ = !isVisible;
    if (this.div_) {
        this.div_.style.visibility = (this.isHidden_ ? 'hidden' : 'visible');
    }
};

/**
 * Returns the content of the InfoBox.
 * @returns {string}
 */
InfoBox.prototype.getContent = function() {
    return this.content_;
};

/**
 * Returns the geographic location of the InfoBox.
 * @returns {LatLng}
 */
InfoBox.prototype.getPosition = function() {
    return this.position_;
};

/**
 * Returns the zIndex for the InfoBox.
 * @returns {number}
 */
InfoBox.prototype.getZIndex = function() {
    return this.zIndex_;
};

/**
 * Returns a flag indicating whether the InfoBox is visible.
 * @returns {boolean}
 */
InfoBox.prototype.getVisible = function() {
    let isVisible;

    if ((typeof this.getMap() === 'undefined') || (this.getMap() === null)) {
        isVisible = false;

    } else {
        isVisible = !this.isHidden_;
    }

    return isVisible;
};

/**
 * Shows the InfoBox. [Deprecated; use <tt>setVisible</tt> instead.]
 */
InfoBox.prototype.show = function() {
    this.isHidden_ = false;
    if (this.div_) {
        this.div_.style.visibility = 'visible';
    }
};

/**
 * Hides the InfoBox. [Deprecated; use <tt>setVisible</tt> instead.]
 */
InfoBox.prototype.hide = function() {
    this.isHidden_ = true;
    if (this.div_) {
        this.div_.style.visibility = 'hidden';
    }
};

/**
 * Adds the InfoBox to the specified map or Street View panorama. If <tt>anchor</tt>
 *  (usually a <tt>google.maps.Marker</tt>) is specified, the position
 *  of the InfoBox is set to the position of the <tt>anchor</tt>. If the
 *  anchor is dragged to a new location, the InfoBox moves as well.
 *
 * @param {Map|StreetViewPanorama} map
 * @param {MVCObject}             [anchor]
 */
InfoBox.prototype.open = function(map, anchor) {
    let me = this;

    if (anchor) {
        this.position_     = anchor.getPosition();
        this.moveListener_ = google.maps.event.addListener(anchor, 'position_changed', function() {
            me.setPosition(this.getPosition());
        });
    }

    this.setMap(map);

    if (this.div_) {
        this.panBox_();
    }
};

/**
 * Removes the InfoBox from the map.
 */
InfoBox.prototype.close = function() {
    this.clearDomEventListeners();

    if (this.moveListener_) {
        google.maps.event.removeListener(this.moveListener_);
        this.moveListener_ = null;
    }

    this.setMap(null);
};
