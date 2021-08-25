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
 * Matching question renderer class.
 *
 * @package   qtype_crossword
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for matching questions.
 *
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_crossword_renderer extends qtype_with_combined_feedback_renderer
{

    public function formulation_and_controls(question_attempt $qa, question_display_options $options)
    {
        global $PAGE;
        $question = $qa->get_question();
        $stemorder = $question->get_stem_order();
        $response = $qa->get_last_qt_data();

        $choices = $this->format_choices($question);
        $arr = [];
        foreach ($stemorder as $key => $stemid) {
            $ques = strip_tags($this->format_stem_text($qa, $stemid));
            $ans = trim($choices[$question->get_right_choice_for($stemid)]);
            $filename = $qa->get_qt_field_name('sub' . $key);
            array_push($arr, [$ans, $ques, $filename]);
        };
        $result = '';
        $result .= '
                    <link rel="stylesheet" href="' . $CFG->dirroot . '/question/type/crossword/styles.css">
                    ';

        $PAGE->requires->js_call_amd('qtype_crossword/crossword', 'setup', array(json_encode($arr)));


        $result .= '<div id="root" class="root">
                    </div>

                    <div id="lists" class="lists">

                        <table>

                            <tr>
                                <td width="50%" id="left-list" valign="top" class="list-text">
                                    <center>
                                    <h3>Across</h3>
                                    </center>
                                </td>
                                <td width="50%" id="right-list" valign="top" class="list-text">
                                    <center>
                                    <h3>Down</h3>
                                    </center>
                                </td>
                            </tr>
                        </table>
                    </div>
        ';

//        $result .= html_writer::tag('div', $question->format_questiontext($qa),
//            array('class' => 'qtext'));
//
//        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
//        $result .= html_writer::start_tag('table', array('class' => 'answer'));
//        $result .= html_writer::start_tag('tbody');
//
//        $parity = 0;
//        $i = 1;
//        foreach ($stemorder as $key => $stemid) {
//
//            $result .= html_writer::start_tag('tr', array('class' => 'r' . $parity));
//            $fieldname = 'sub' . $key;
//
//            $result .= html_writer::tag('td', $this->format_stem_text($qa, $stemid),
//                array('class' => 'text'));
//
//            $classes = 'control';
//            $feedbackimage = '';
//
//            if (array_key_exists($fieldname, $response)) {
//                $selected = $response[$fieldname];
//            } else {
//                $selected = 0;
//            }
//
//            $fraction = (int) ($selected && $selected == $question->get_right_choice_for($stemid));
//
//            if ($options->correctness && $selected) {
//                $classes .= ' ' . $this->feedback_class($fraction);
//                $feedbackimage = $this->feedback_image($fraction);
//            }
//
//            $result .= html_writer::tag('td',
//                html_writer::label(get_string('answer', 'qtype_crossword', $i),
//                    'menu' . $qa->get_qt_field_name('sub' . $key), false,
//                    array('class' => 'accesshide')) .
//                html_writer::select($choices, $qa->get_qt_field_name('sub' . $key), $selected,
//                    array('0' => 'choose'), array('disabled' => $options->readonly, 'class' => 'custom-select ml-1')) .
//                ' ' . $feedbackimage, array('class' => $classes));
//
//            $result .= html_writer::end_tag('tr');
//            $parity = 1 - $parity;
//            $i++;
//        }
//        $result .= html_writer::end_tag('tbody');
//        $result .= html_writer::end_tag('table');
//
//        $result .= html_writer::end_tag('div'); // Closes <div class="ablock">.
//
//        if ($qa->get_state() == question_state::$invalid) {
//            $result .= html_writer::nonempty_tag('div',
//                $question->get_validation_error($response),
//                array('class' => 'validationerror'));
//        }


        $result .= "
        <script>
	function onBlurFuntion(data,x,y,across){
		
		var answer = data.toLowerCase();

		if (answer) {
			var across = across;

			var x = parseInt(x, 10);
			var y = parseInt(y, 10);

			if (across && across != 'false') {
				for (var i = 0; i < answer.length; i++) {
					var newheight = y + i;
					var letterposition = 'letter-position-' + x + '-' + newheight;
					$('#' + letterposition).text(answer[i]);
				}
			} else {
				for (var i = 0; i < answer.length; i++) {
					var newwidth = x + i;
					var letterposition = 'letter-position-' + newwidth + '-' + y;
					$('#' + letterposition).text(answer[i]);
				}
			}

			// $('#' + word + '-listing').addClass('strikeout');
			// $('#' + word + '-listing').attr('data-solved', true);

			$('#answer-form').hide();
		} else {
			if (!$('#answer-results').is(':visible')) {
				$('#answer-results').show();
				$('#answer-results').html('Incorrect Answer, Please Try Again');
			}
		}

		return false;
	}
</script>
";

        return $result;
    }

    public function specific_feedback(question_attempt $qa)
    {
        return $this->combined_feedback($qa);
    }

    /**
     * Format each question stem. Overwritten by randomsamatch renderer.
     *
     * @param question_attempt $qa
     * @param integer $stemid stem index
     * @return string
     */
    public function format_stem_text($qa, $stemid)
    {
        $question = $qa->get_question();
        return $question->format_text(
            $question->stems[$stemid], $question->stemformat[$stemid],
            $qa, 'qtype_crossword', 'subquestion', $stemid);
    }

    protected function format_choices($question)
    {
        $choices = array();
        foreach ($question->get_choice_order() as $key => $choiceid) {
            $choices[$key] = format_string($question->choices[$choiceid]);
        }
        return $choices;
    }

    public function correct_response(question_attempt $qa)
    {
        $question = $qa->get_question();
        $stemorder = $question->get_stem_order();

        $choices = $this->format_choices($question);
        $right = array();
        foreach ($stemorder as $key => $stemid) {
            if (!isset($choices[$question->get_right_choice_for($stemid)])) {
                continue;
            }
            $right[] = $question->make_html_inline($this->format_stem_text($qa, $stemid)) . ' &#x2192; ' .
                $choices[$question->get_right_choice_for($stemid)];
        }

        if (!empty($right)) {
            return get_string('correctansweris', 'qtype_crossword', implode(', ', $right));
        }
    }
}
