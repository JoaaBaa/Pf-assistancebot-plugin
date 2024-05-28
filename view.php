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
require('C:\xampp\htdocs\moodle\mod\attendance\classes\attendance_webservices_handler.php');

// Set the time zone to Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

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


const PORC_TIEMPO_REQUERIDO = 0.75; // hay que obtenerlo del mod_form
const MINUTOS_TOLERANCIA_TARDE = 20;

 // funciones para calcular si el alumno estuvo presente, hay que modificarlas para que reciban parametros del formulario 
 function llegoTarde($result) {
    global $comienzoReunion;
    return (($result['join_time'] / 60) - ($comienzoReunion / 60)) > 20;
}

function estuvoTiempoRequerido($total_duracion, $porcentaje) {
    global $duracion_reunion;

    $estuvo = false;
    $minutos_participante = $total_duracion / 60;
    
    $porcentaje_requerido = $porcentaje * $duracion_reunion;

    if ($minutos_participante >= $porcentaje_requerido) {
        $estuvo = true;
    }
    return $estuvo;
}

echo $OUTPUT->header();

$test_date = '2024-05-27'; 
$startTime = strtotime($test_date . ' 00:00:00');

// Convertir la fecha al formato UNIX timestamp del final del día
$endTime = strtotime($test_date . ' 23:59:59');

// Obtener todas las reuniones de zoom del día de hoy filtrando por el nombre de la materia
$sql = "SELECT zd.*, z.name, z.course
        FROM {zoom_meeting_details} zd
        JOIN {zoom} z ON zd.zoomid = z.id
        WHERE z.course = :courseid AND 
        DATE(FROM_UNIXTIME(zd.start_time)) = :test_date"; 

$params = [
    'test_date' => $test_date,
    'courseid' => $course->id
];

$meetings = $DB->get_records_sql($sql, $params);
print_r($meetings);

echo "<br>";
echo "hasta aca sin filtrar";
echo "<br>";

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

    // Convertir start_time y end_time a objetos DateTime en la zona horaria de Argentina
    $startDateTime = new DateTime("@$start_time");
    $startDateTime->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));

    $endDateTime = new DateTime("@$end_time");
    $endDateTime->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));

    // Extraer la hora de startDateTime y endDateTime
    $startHour = (int)$startDateTime->format('G');  // Hora en formato 24 horas (0-23)
    $startMinute = (int)$startDateTime->format('i');  // Minuto (0-59)
    $endHour = (int)$endDateTime->format('G');
    $endMinute = (int)$endDateTime->format('i');

    // Definir el rango horario de 18:30 a 23:00 habria que pasarlo por parametro quizas segun cada materia 
    $rangeStartHour = 18;
    $rangeStartMinute = 30;
    $rangeEndHour = 23;
    $rangeEndMinute = 30;

    // Verificar si la reunión está dentro del rango horario
    $isWithinRange = (
        ($startHour > $rangeStartHour || ($startHour == $rangeStartHour && $startMinute >= $rangeStartMinute)) &&
        ($endHour < $rangeEndHour || ($endHour == $rangeEndHour && $endMinute <= $rangeEndMinute))
    );

    if ($isWithinRange) {
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

            // Agregar el nuevo id al array de ids
            $filteredMeetings->$zoomid->ids[] = $id;
        } else {
            // Si la reunión no existe, agregar un nuevo registro
            $filteredMeetings->$zoomid = (object)[
                'ids' => [$id],
                'meeting_id' => $meeting_id,
                'name' => $name,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'duration' => $duration,
                'topic' => $topic,
                'startDateTime' => $startDateTime, 
                'endDateTime' => $startDateTime
            ];
        }
    }
}
// Print the filtered meetings
//print_r($filteredMeetings);

foreach ($filteredMeetings as $zoomid => $meeting) { 
    $full_name = $meeting->name;

if (strpos($full_name, $course->fullname) === 0) {
    $group_name = trim(str_replace($course->fullname, '', $full_name));
     
    $course_name = $course->fullname;

    if (preg_match('/^(BE|YA)-[A-Za-z0-9]{3,4}$/', $group_name)) {
    } else {
        $group_name = 0; 
    }
} else {
    $group_name = 0;
}
    echo "ReunionCurso: " . $course_name . " | Grupo: " . $group_name . "\n".  " Duración: " . $meeting->duration . "\n" . '<br>';


//obtener participantes

foreach($meeting->ids as $id) {
    $participants = [];
    $meeting_participants = $DB->get_records_select(
        'zoom_meeting_participants', // Tabla
        'detailsid = ?',             // Condición WHERE
        array($id),          // Parámetros para la condición
        '',                         
        'id, name, duration, join_time, leave_time, user_email, userid' // Campos seleccionados
    );
   $meeting_participants = json_decode( json_encode($meeting_participants), true);
    $participants = array_merge($participants, $meeting_participants);
}
    //print_r($participants);


    // Procesar cada participante
      
    $results = []; // Inicializar el array $results
    
    foreach ($participants as $participant) {
        $email = $participant['user_email'];
        $name = $participant['name'];
        $duration = $participant['duration'];
        $join_time = $participant['join_time'];
        $userid = $participant['userid'];

        // Si el email ya existe en el array resultante, sumar la duración y tomar el join_time más temprano
        if (isset($results[$name])) {
            // Sumar la duración
            $results[$name]['duration'] += $duration;
    
            // Actualizar el join_time al más temprano
            if ($join_time < $results[$name]['join_time']) {
                $results[$name]['join_time'] = $join_time;
            }
        } else {
            // Si el email no existe, agregar un nuevo registro
            $results[$name] = [
                'join_time' => $join_time,
                'name' => $name,
                'user_email' => $email,
                'duration' => $duration, 
                'userid'=> $userid
            ];
        }
    }


         // si la reunion esta asignada a un grupo especifico, podria obtener los cursantes de ese grupo  
    if($group_name !== 0) {
        $group = $DB->get_record('groups', array('name' => $group_name, 'courseid' => $course->id));
     
        $cursantes = groups_get_members($group->id); 
    }
else {
    $group->id = 0;
    $context = context_course::instance($course->id); 
    $cursantes = get_enrolled_users($context);
};

// obtener la sesion de asistencia
$courseid = $course->id;
$groupid = $group->id;

echo 'curso: ' .$courseid . 'grupo: '. $groupid ;
try{
    $courseid = $course->id;
    $groupid = $group->id;

    $sql = "SELECT *
    FROM {attendance_sessions} a
    WHERE attendanceid IN (SELECT id FROM {attendance} WHERE course = :courseid) 
    AND groupid = :groupid
    AND DATE(FROM_UNIXTIME(a.sessdate)) = :test_date";


$params = [
    'courseid' => $courseid,
    'groupid' => $groupid,
    'test_date' => $test_date
];

$session = $DB->get_record_sql($sql, $params);
} catch(error){}

print_r($session);


$userid = 2;

$comienzoReunion = $meeting->start_time;
$duracion = $meeting->duration;

$sessionid = $session->id;
$attendanceid = $session->attendanceid;

echo 'ID de la session: ' .$sessionid;


if(!empty($session)) {

$statuses = $DB->get_records_sql("
    SELECT at.id
    FROM {attendance_statuses} AS at
    WHERE at.attendanceid = ?", array($attendanceid));

// Inicializar una cadena para almacenar los IDs
$statuses_string = '';

// Recorrer los registros y concatenar los IDs
foreach ($statuses as $status) {
    $statuses_string .= $status->id . ',';
}
$statuses_string = rtrim($statuses_string, ', ');

$actualizaciones = array();

$status_ausente = $DB->get_field_select(
    'attendance_statuses',
    'id',
    'attendanceid = ? AND acronym = ? ',
    array($attendanceid, 'FI')
);
echo $status_ausente. ' <br>';
$status_tarde= $DB->get_field_select(
    'attendance_statuses',
    'id',
    'attendanceid = ? AND acronym = ? ',
    array($attendanceid, 'R')
);
echo $status_tarde. ' <br>';
$status_presente = $DB->get_field_select(
    'attendance_statuses',
    'id',
    'attendanceid = ? AND acronym = ? ',
    array($attendanceid, 'P')
);

foreach ($cursantes as $cursante) {
    $cursanteEncontrado = false;
    $cursantePresente = false;
    $fullname = trim($cursante->lastname . ' ' . $cursante->firstname);
    $fullname2 = trim($cursante->firstname . ' ' . $cursante->lastname);
    
   foreach ($results as $result) {
       
         if ((strcasecmp($result['name'], $fullname) == 0 || strcasecmp($result['name'], $fullname2) == 0) 
  // && strcasecmp($result['user_email'], $cursante->email) == 0  lo estoy sacando por que no todos los participantes tienen el email
   
   ) {
       $cursanteEncontrado = true;     
       echo 'paso por aca';                          
   if(estuvoTiempoRequerido($result['duration'] , PORC_TIEMPO_REQUERIDO))  {
       echo 'paso por estuvo tiempo requerido'; 
   if(!llegoTarde($result)) {
       echo 'paso por llegada tarde'; 
       $cursantePresente = true;
       $contador += 1;
        echo $contador ."". ")" ;
        // aca habria que pasar los datos a las tablas de attendance, sessionid de attendance se podria obtener con el grupo y la fecha de la reunion 
         echo "El cursante {$fullname}, con el correo {$cursante->email} asistió a la reunión y estuvo el tiempo requerido por el presente. duracion = {$result['duration']} segundos";
          echo "<br>";
          
          $actualizacion = array(
           'sessionid' => $sessionid,'studentid' => $cursante->id,'takenbyid' => $userid,'statusid' => $status_presente,'statusset' => $statuses_string
           //'remark' => 'ORT ASSISTANCE BOT'
       );
       $actualizaciones[] = $actualizacion;
         break;
   }
   else {

       $cursantePresente = true;
       $contador += 1;
        echo $contador ."". ")" ;
         echo "El cursante {$fullname}, con el correo {$cursante->email} asistió a la reunión pero llego tarde y estuvo el tiempo requerido por el presente. {duracion = {$result['duration']} segundos";
          echo "<br>";
          $actualizacion = array('sessionid' => $sessionid,'studentid' => $cursante->id,'takenbyid' => $userid,'statusid' => $status_tarde,'statusset' => $statuses_string
       //'remark' => 'ORT ASSISTANCE BOT'
       );
       $actualizaciones[] = $actualizacion;
     
         break;
   }
}
       }  
   } 
   if (!$cursanteEncontrado|| !$cursantePresente) { 
       
       $contador += 1;
       echo $contador ."". ")" ;
       echo "El cursante {$fullname}, con el correo {$cursante->email} no asistió a la reunión.";
       echo "<br>";

       $actualizacion = array(
           'sessionid' => $sessionid,'studentid' => $cursante->id,'takenbyid' => $userid,'statusid' => $status_ausente,'statusset' => $statuses_string//'remark' => 'ORT ASSISTANCE BOT'
       );
       $actualizaciones[] = $actualizacion;
   }

      
   } 
   
    foreach ($actualizaciones as $actualizacion) {
        
        attendance_handler::update_user_status($actualizacion['sessionid'], $actualizacion['studentid'], $actualizacion['takenbyid'], $actualizacion['statusid'], $actualizacion['statusset']);
        echo '<br>';
        echo 'taking assistance: DONE';
    } }  else {echo 'no se encontro la session';}
}
echo $OUTPUT->footer();