// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Register a link handler to open mod/treasurehunt/view.php links in the app
 *
 * @package    mod_treasurehunt
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var that = this;

/** Functions */
this.changeBaseLayer = (selectedBaseLayer) => {
  // Update layersConfig state
  this.layersConfig.baseLayers.forEach((baseLayer) => {
    if (baseLayer.name === selectedBaseLayer) {
      baseLayer.visible = true;
    } else {
      baseLayer.visible = false;
    }
  });
  this.setActiveBaseLayer(selectedBaseLayer);
  this.NavController.pop();
};

this.changeOverlayLayer = (layerName, visible) => {
  this.setOverlayLayerVisibility(layerName, visible);
};
