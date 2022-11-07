<?php
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
 * @package customfield_cost
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace customfield_cost;

defined('MOODLE_INTERNAL') || die;

class data_controller extends \core_customfield\data_controller {

    /**
     * Return the name of the field where the information is stored
     * @return string
     */
    public function datafield(): string {
        return 'decvalue';
    }

    /**
     * Add fields for editing data of a date field on a context.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $config = $field->get('configdata');
        $elementname = $this->get_form_element_name();

        $allowedcurrencies = array_combine($config['currencies'], $config['currencies']);
        $options = array_intersect_key(get_string_manager()->get_list_of_currencies(), $allowedcurrencies);

        $currencyelement = $mform->createElement('select', "{$elementname}_currency", get_string('currency', 'customfield_cost'), $options);
        $costelement = $mform->createElement('float', "{$elementname}_amount", get_string('amount', 'customfield_cost'), array('size' => '10'));
        $mform->addGroup([
            $currencyelement,
            $costelement
        ], $elementname, $this->get_field()->get_formatted_name());
        $mform->setType("{$elementname}_currency", PARAM_TEXT);
        $mform->setType("{$elementname}_amount", PARAM_FLOAT);

        if ($field->get_configdata_property('required')) {
         //   $mform->addRule("{$elementname}_currency", null, 'required', null, 'client');
      //      $mform->addRule("{$elementname}_amount", null, 'required', null, 'client');
        }
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return 0;
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (
            !property_exists($datanew, $elementname)
            ||
            !isset($datanew->{$elementname}["{$elementname}_currency"])
            ||
            !isset($datanew->{$elementname}["{$elementname}_amount"])
        ) {
            return;
        }
        $this->data->set('shortcharvalue', $datanew->{$elementname}["{$elementname}_currency"]);
        $this->data->set('decvalue', $datanew->{$elementname}["{$elementname}_amount"]);
        $this->save();
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        return number_format($this->get('decvalue'), 2) . ' ' . $this->get('shortcharvalue');
    }

    /**
     * Called from instance edit form in definition_after_data()
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition_after_data(\MoodleQuickForm $mform) {
        $elementname = $this->get_form_element_name();

        if (!$mform->elementExists("$elementname")) {
            return;
        }

        $param = $mform->getElement("{$elementname}");

        if (!empty($this->get('shortcharvalue'))) {
            $param->_elements[0]->setValue($this->get('shortcharvalue'));
        }


        if (!empty($this->get('decvalue'))) {
            $param->_elements[1]->setValue(number_format($this->get('decvalue'), 2));
        }

        parent::instance_form_definition_after_data($mform);
    }
}
