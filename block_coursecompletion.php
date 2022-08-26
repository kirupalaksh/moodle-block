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
 * Block for generate report for course completion students particular input dates.
 *
 * @package   block_coursecompletion
 * @copyright 2022 onwards Kirupa Lakshmi 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_coursecompletion extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_coursecompletion');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
     return array(
                'admin' => false,
                'site-index' => false,
                'course-view' => false,
                'mod' => false,
                'my' => true
        );
     }

    function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newhtmlblock', 'block_coursecompletion');
        }
    }

    function instance_allow_multiple() {
        return true;
    }
	
	protected function specific_definition($mform) {
        global $CFG;

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_html'));
        $mform->setType('config_title', PARAM_TEXT);


    }


    function get_content() {
	    global $CFG ,$PAGE, $USER, $DB, $mform;
		require_once($CFG->libdir . '/filelib.php');
		require_once('add_form.php');
		
		$webroot = $CFG->wwwroot;

		$mform = new block_add_form();
		$this->content = new stdClass;
		$this->page->requires->js(new moodle_url('https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js'), true); 
		$this->page->requires->css(new moodle_url('https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css')); 
		$this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/coursecompletion/js/mydatatable.js'));

		//Form processing and displaying is done here
		if ($fromform = $mform->get_data()) { 
			//In this case you process validated data. 
			$sem_from 	=  $fromform->assesstimestart;
			$sem_to   	=  $fromform->assesstimefinish;
			$from_date = date('d-m-Y', $sem_from); 
			$to_date = date('d-m-Y', $sem_to); 	
		$this->config->text .= '<div class="card custom_title" style="background-color:rgba(47, 93, 176, 0.16);">';
		$this->config->text .= '<p style="text-align:center;">'.'Course Completion Report';	
		$this->config->text .= '<p style="text-align:center;">'. $from_date.'&nbsp; to &nbsp;'.$to_date.'</p>';
		$this->config->text .= '</div>'; 
		$this->config->text .=  '<table id="example" class="display" style="width:100%">';
		$this->config->text .=  '<thead><tr><th>Category</th><th>Course Name</th><th>Total No of Participants</th><th>Completion</th><th>Average</th></tr></thead>';
		$this->config->text .=  '<tbody>';
				
		$sql1="SELECT a.id,a.fullname,b.name FROM {course} a inner join {course_categories} b on a.category = b.id  order by a.fullname";
		$records1=$DB->get_records_sql($sql1);
		$completion_count_open =0;
		$i=0;
		$f=1;$k=0;
		$c_e_tot_avg=0;
		foreach($records1 as $key=>$datas){
		
		$courseid = $datas->id;
		$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
		$context = context_course::instance($course->id);
		 $sql_participant_count="SELECT count(u.id) as total_countt,c.id, u.lastname AS lastname,r.name,r.id
			FROM {course} c
			JOIN {context} ct ON c.id = ct.instanceid
			JOIN {role_assignments} ra ON ra.contextid = ct.id
			JOIN {user} u ON u.id = ra.userid
			JOIN {role} r ON r.id = ra.roleid
			where c.id = '".$datas->id."' AND r.id=5 ";
		$records_participant_count=$DB->get_records_sql($sql_participant_count);
		
		foreach ($records_participant_count as $key=>$count_datas){
			 $total_participants = $key;
		}
		
		$sql_grade = "SELECT u.id,u.firstname,u.lastname,b. * FROM {grade_items} a
			INNER JOIN {grade_grades} b ON b.itemid = a.id
			INNER JOIN {user} u ON b.userid = u.id
			WHERE a.itemname IN ('Final Quiz','Final Assignment','Final Assignment ~ The Restaurant') AND a.courseid ='".$datas->id."'
		AND b.timemodified between '".$sem_from."' AND '".$sem_to."'";
		
		$records_grade=$DB->get_records_sql($sql_grade);
		
		$total_completion = count($records_grade);
		//if(!$records_grade){
		//}
		//else{
		$sum=0;
			foreach($records_grade as $final_grade){
				//course ovell score 
				$sql_final_grade ="SELECT a.finalgrade ,c.id as userid
									FROM  {grade_grades} a
									INNER JOIN {grade_items} b ON a.itemid = b.id
									INNER JOIN {user} c ON c.id = a.userid
									WHERE b.courseid ='".$datas->id."'
									AND c.id ='".$final_grade->userid."' order by a.itemid asc limit 0,1";	
												
				$final_records_grade=$DB->get_records_sql($sql_final_grade);
				
				foreach($final_records_grade as $key =>$final_all_score){
					$course_total = $key;
					if($course_total!=""){
						$course_total=$course_total;
					}else{
						$course_total = 0;
					}
					
				}
				$course_avg_total = round($course_total,2);
				$sum += $course_avg_total;
				
			
		
		
		
			$completion_count_open =$completion_count_open+$total_completion;
			
			$total_comp = $total_completion*100;
			$average = ($sum*100)/$total_comp;
			echo "<script>
			document.getElementById('avg".$i."').innerHTML='".round($average,2)."';
			</script>";
			$avg_total = round($average,2);
			$c_c_tot_avg += $avg_total; 
			$c_c_tot_count = $h;			
			$h++;				
			$this->config->text .= '<tr><th>'.'<a target="blank" href="'.$webroot.'/course/view.php?id='.$datas->id.'">'.$datas->name.'</a></th>';
			$this->config->text .= '<th>'.$datas->fullname.'</th><th>'.$total_participants.'</th><th>'.$total_completion.'</th>';
			//$this->config->text .= '<th>'.is_nan($average).'</th></tr>';
			$this->config->text .= '<th><span id="avg'.$i.'">'.$average.'</span></th></tr>';
		}	
		$i++;
		//}		
	}$k++;	
		$this->config->text .= '</tbody></table>';
		$this->content->text = $this->config->text;
		return $this->content;
			
		} else {
		if (has_capability('block/coursecompletion:addinstance', $this->context)) {
					$this->content->text = $mform->render();			
		}
		} //return $this->content;
   } // function ends
}
