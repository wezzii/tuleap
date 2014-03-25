<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
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

class ArtifactStaticListFieldXMLExporter extends ArtifactAlphaNumFieldXMLExporter {
    const TV3_DISPLAY_TYPE = 'SB';
    const TV3_DATA_TYPE    = '2';
    const TV3_VALUE_INDEX  = 'valueInt';
    const TV3_TYPE         = 'SB_2';
    const TV5_TYPE         = 'list';
    const TV5_BIND         = 'static';

    const SPECIAL_SEVERITY = 'severity';

    /** @var ArtifactXMLExporterDao */
    protected $dao;

    public function __construct(ArtifactXMLNodeHelper $node_helper, ArtifactXMLExporterDao $dao) {
        parent::__construct($node_helper);
        $this->dao = $dao;
    }

    public function appendNode(DOMElement $changeset_node, $tracker_id, $artifact_id, array $row) {
        $field_node = $this->getNode(self::TV5_TYPE, $row);
        $field_node->setAttribute('bind', self::TV5_BIND);
        $field_node->appendChild($this->getNodeValue($this->getValueLabel($tracker_id, $artifact_id, $row['field_name'], $row['new_value'])));
        $changeset_node->appendChild($field_node);
    }

    private function getValueLabel($tracker_id, $artifact_id, $field_name, $value) {
        if ($field_name == self::SPECIAL_SEVERITY && $value == 0) {
            return '';
        }
        if ($value == 100) {
            return '';
        }
        foreach ($this->dao->searchFieldValuesList($tracker_id, $field_name) as $row) {
            if ($row['value_id'] == $value) {
                return $row['value'];
            }
        }

        throw new Exception_TV3XMLException("Unknown label for $artifact_id $value");
    }

    public function getCurrentFieldValue(array $field_value_row, $tracker_id) {
        if ($field_value_row['field_name'] != self::SPECIAL_SEVERITY) {
            return parent::getCurrentFieldValue($field_value_row, $tracker_id);
        }
    }

    public function getFieldValueIndex() {
        return self::TV3_VALUE_INDEX;
    }

}
