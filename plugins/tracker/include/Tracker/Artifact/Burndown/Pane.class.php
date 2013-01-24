<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

class Tracker_Artifact_Burndown_PaneInfo extends AgileDashboard_PaneInfo {
    const IDENTIFIER = 'burndown';
    const TITLE = 'Burndown';

    public function getUri() {
        return parent::getUri().'&pane='.$this->getIdentifier();
    }


    /**
     * @see AgileDashboard_Pane::getIdentifier()
     * @return string
     */
    public function getIdentifier() {
        return self::IDENTIFIER;
    }

    /**
     * @see AgileDashboard_Pane::getTitle()
     * @return string
     */
    public function getTitle() {
        return self::TITLE;
    }
    
    /**
     * @see AgileDashboard_Pane::getIcon()
     */
    public function getIcon() {
        return '';
    }

    /**
     * @see AgileDashboard_Pane::getIconTitle()
     */
    public function getIconTitle() {
        return '';
    }
}

/**
 * A pane to be displayed in AgileDashboard
 */
class Tracker_Artifact_Burndown_Pane extends AgileDashboard_Pane {
        
    /**
     * @var Tracker_Artifact
     */
    private $artifact;
    
    /**
     * @var Tracker_FormElement_Field_Burndown
     */
    private $field;
    
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $plugin_theme_path;

    public function __construct(Tracker_Artifact $artifact, Tracker_FormElement_Field_Burndown $field, User $user, $plugin_theme_path) {
        $this->artifact          = $artifact;
        $this->field             = $field;
        $this->user              = $user;
        $this->plugin_theme_path = $plugin_theme_path;
    }

    public function getIdentifier() {
        return Tracker_Artifact_Burndown_PaneInfo::IDENTIFIER;
    }

    public function getUriForMilestone(Planning_Milestone $milestone) {
        parent::getUriForMilestone($milestone).'&pane='.Tracker_Artifact_Burndown_PaneInfo::IDENTIFIER;
    }
    
    
    /**
     * @see AgileDashboard_Pane::getFullContent()
     */
    public function getFullContent() {
        return $this->getPaneContent();
    }

    /**
     * @see AgileDashboard_Pane::getMinimalContent()
     */
    public function getMinimalContent() {
        return '';
    }

    private function getPaneContent() {
        return $this->field->fetchArtifactValueReadOnly($this->artifact);
    }
}
?>
