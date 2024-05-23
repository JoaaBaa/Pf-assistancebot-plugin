<?php
/**
 * Prints an instance of mod_asistbot2.
 *
 * @package     mod_asistbot2
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require(__DIR__.'/../../config.php');
 require_once(__DIR__.'/lib.php');
 
 // Course module id.
 $id = optional_param('id', 0, PARAM_INT);
 
 // Activity instance id.
 $a = optional_param('a', 0, PARAM_INT);
 
 if ($id) {
     $cm = get_coursemodule_from_id('asistbot2', $id, 0, false, MUST_EXIST);
     $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
     $moduleinstance = $DB->get_record('asistbot2', array('id' => $cm->instance), '*', MUST_EXIST);
 } else {
     $moduleinstance = $DB->get_record('asistbot2', array('id' => $a), '*', MUST_EXIST);
     $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
     $cm = get_coursemodule_from_instance('asistbot2', $moduleinstance->id, $course->id, false, MUST_EXIST);
 }
 
 require_login($course, true, $cm);
 
 $modulecontext = context_module::instance($cm->id);
 
 $PAGE->set_url('/mod/asistbot2/view.php', array('id' => $cm->id));
 $PAGE->set_title(format_string($moduleinstance->name));
 $PAGE->set_heading(format_string($course->fullname));
 $PAGE->set_context($modulecontext);
 
 echo $OUTPUT->header();
 
 $test_date = '2020-05-27';
 $today_start = strtotime($test_date . ' 00:00:00');
 $today_end = strtotime($test_date . ' 23:59:59');
 
 // Obtener todas las reuniones de zoom del día de hoy filtrando por el nombre de la materia
 $sql = "SELECT zd.*, z.name 
         FROM {zoom_meeting_details} zd
         JOIN {zoom} z ON zd.zoomid = z.id
         WHERE zd.start_time >= :today_start 
           AND zd.start_time <= :today_end
           AND z.name LIKE :course_name_filter";
 
 $params = [
     'today_start' => $today_start,
     'today_end' => $today_end,
     'course_name_filter' => $course->fullname . '%'
 ];
 
 $meetings = $DB->get_records_sql($sql, $params);
 print_r($meetings);
 //meetings filter
 // Inicializar el objeto $filteredMeetings
$filteredMeetings = new stdClass();

foreach ($meetings as $meeting) {
    $id = $meeting->id;
    $zoomid = $meeting->zoomid;
    $meeting_id = $meeting->meeting_id;
    $start_time = $meeting->start_time;
    $end_time = $meeting->end_time;
    $duration = $meeting->duration;
    $topic = $meeting->topic;
    $name = $meeting->name;

    // Si la meet ya se inició antes (es la misma) vamos a sumar los tiempos
    if (isset($filteredMeetings->$zoomid)) {
        // Sumar la duración
        $filteredMeetings->$zoomid->duration += $duration;

        // Actualizar el start_time al más temprano y el end_time al más tardío
        if ($start_time < $filteredMeetings->$zoomid->start_time) {
            $filteredMeetings->$zoomid->start_time = $start_time;
        }
        if ($end_time > $filteredMeetings->$zoomid->end_time) {
            $filteredMeetings->$zoomid->end_time = $end_time;
        }

    } else {
        // Si la meet no existe, agregar un nuevo registro
        $filteredMeetings->$zoomid = (object)[
            'id' => $id,
            'meeting_id' => $meeting_id,
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration' => $duration,
            'topic' => $topic
        ];
    }
}

// Print the filtered meetings
print_r($filteredMeetings);
 
 // No need to convert to array, continue using objects
 foreach ($filteredMeetings as $meeting) {  // si la meeting se corto por ejemplo, hay dos con el mismo zoomid y habria que sumar su duracion
     $full_name = $meeting->name;
 
     if (strpos($full_name, $course->fullname) === 0) {
         $group_name = trim(str_replace($course->fullname, '', $full_name));
         $course_name = $course->fullname;
     } else {
         $group_name = 'no hay grupos';
     }
 
     echo "Reunión ID: " . $meeting->id . " | Tema: " . $meeting->topic . " | Inicio: " . date('Y-m-d H:i:s', $meeting->start_time) . " | Fin: " . date('Y-m-d H:i:s', $meeting->end_time) . "\n";
     echo "Curso: " . $course_name . " | Grupo: " . $group_name . "\n";
 }
 
 echo $OUTPUT->footer();
 