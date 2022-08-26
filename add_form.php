<?php
//moodleform is defined in formslib.php
require_once($CFG->libdir.'/formslib.php');

class block_add_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 
		$mform->addElement('date_selector', 'assesstimestart', get_string('from'));
		$year = '2022';
		$month = '01';
		$day = '10';
		$defaulttime = make_timestamp($year, $month, $day);
		$mform->setDefault('assesstimestart',  $defaulttime);
		$mform->addElement('date_selector', 'assesstimefinish', get_string('to',));	
		$month1 = '09';
		$defaulttime1 = make_timestamp($year, $month1, $day);
		$mform->setDefault('assesstimefinish',  $defaulttime1);		
		$this->add_action_buttons($cancel = false, $submitlabel='Submit');

           
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
