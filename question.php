<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Question definition class for sheet.
 *
 * @package     qtype_sheet
 * @copyright   2024 Ivan Abella abellawebdev@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

/**
 * Class that represents a sheet question.
 */
class qtype_sheet_question extends question_with_responses {

    // Properties:
    public $spreadsheetdata;

    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_essay_format_renderer_base the response-format-specific renderer.
     */

    public function get_expected_data() {
        return ['spreadsheet' => PARAM_RAW];
    }

    public function is_complete_response(array $response) {
        return !empty($response['spreadsheetdata']); 
    }

    public function summarise_response(array $response) {
        return question_utils::to_plain_text($response['spreadsheetdata'], FORMAT_HTML); 
    }

    public function un_summarise_response(string $summary) {
        return ['spreadsheetdata' => $summary];
    }

    public function is_gradable_response(array $response) {
        return !empty($response['spreadsheetdata']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) { 
        $prevAnswer = array_key_exists('spreadsheetdata', $prevresponse) && $prevresponse['spreadsheetdata'] !== $this->spreadsheetdata 
            ? (string) $prevresponse['spreadsheetdata'] 
            : '';

        $newAnswer = array_key_exists('spreadsheetdata', $newresponse) && $newresponse['spreadsheetdata'] !== $this->spreadsheetdata 
            ? (string) $newresponse['spreadsheetdata'] 
            : '';
        return $prevAnswer === $newAnswer;
    }
    
    public function get_question_definition_for_external_rendering(question_attempt $qa, question_display_options $options) {
        $settings = [
            'spreadsheetdata' => $this->spreadsheetdata,
        ];
        return $settings;
    }

    public function get_correct_response() {
        return null;
    }

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }
}