
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

require_once($CFG->dirroot . '/mod/attendance/locallib.php');
require_once($CFG->dirroot . '/mod/attendance/lib.php');

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


$record = $DB->get_record('asistbot2', array('course' => $course->id), 'attendancepercentage');
$porcRequerido = $record->attendancepercentage / 100;

$minutos_tolerancia_tarde = 20; // esto tambien hay que sacarlo del form

$prefijo_grupos_a_considerar;

function add_attendance_session($attendanceid, $groupid, $duration, $sessdate, $description = '') {
    global $DB;

    // Cargar el objeto attendance
    $attendance = $DB->get_record('attendance', array('id' => $attendanceid), '*', MUST_EXIST);
    
    // Obtener el último valor de caleventid
    $last_caleventid = $DB->get_field_sql('SELECT MAX(caleventid) FROM {attendance_sessions}');
    $new_caleventid = $last_caleventid + 1;

    // Crear un objeto de sesión
    $session = new stdClass();
    $session->attendanceid = $attendanceid;
    $session->groupid = $groupid;
    $session->sessdate = $sessdate;
    $session->duration = $duration;
    $session->description = $description;
    $session->descriptionformat = 1; // o FORMAT_MOODLE, FORMAT_PLAIN, etc.
    $session->studentscanmark = 0;
    $session->studentsearlyopentime = 0;
    $session->autoassignstatus = 0;
    $session->studentpassword = '';
    $session->subnet = '';
    $session->automark = 0;
    $session->automarkcompleted = 0;
    $session->statusset = 0;
    $session->absenteereport = 1;
    $session->preventsharedip = 0;
    $session->preventsharediptime = 0;
    $session->caleventid = $new_caleventid;
    $session->calendarevent = 1;
    $session->includeqrcode = 0;
    $session->rotateqrcode = 0;
    $session->rotateqrcodesecret = '';
    $session->automarkcmid = 0;
    $session->allowupdatestatus = 0;

    // Insertar la sesión en la base de datos
    $DB->insert_record('attendance_sessions', $session);

    echo "La sesión de asistencia ha sido agregada exitosamente.";
}

function update_user_status($sessionid, $studentid, $takenbyid, $statusid, $statusset) {
    global $DB;

    $record = new stdClass();
    $record->statusset = $statusset;
    $record->sessionid = $sessionid;
    $record->timetaken = time();
    $record->takenby = $takenbyid;
    $record->statusid = $statusid;
    $record->studentid = $studentid;

    if ($attendancelog = $DB->get_record('attendance_log', array('sessionid' => $sessionid, 'studentid' => $studentid))) {
        $record->id = $attendancelog->id;
        $DB->update_record('attendance_log', $record);
    } else {
        $DB->insert_record('attendance_log', $record);
    }

    if ($attendancesession = $DB->get_record('attendance_sessions', array('id' => $sessionid))) {
        $attendancesession->lasttaken = time();
        $attendancesession->lasttakenby = $takenbyid;
        $attendancesession->timemodified = time();

        $DB->update_record('attendance_sessions', $attendancesession);
    }
}

 // funciones para calcular si el alumno estuvo presente, hay  que modificarlas para que reciban parametros del formulario 
 function llegoTarde($participante, $comienzoReunion) {
    return (($participante['join_time'] / 60) - ($comienzoReunion / 60)) > 20;
}

function estuvoTiempoRequerido($duracionParticipante, $porcentaje, $duracion_reunion) {

    
    $estuvo = false;
    $minutos_participante = $duracionParticipante / 60;
    
    $tiempo_requerido = $porcentaje * $duracion_reunion;

    if ($minutos_participante >= $tiempo_requerido) {
        $estuvo = true;
    }
    return $estuvo;
}

function camaraEncendida ($participante) { // aplicaria en caso que se encuentre la forma de saber si el participante tuvo prendida la camara 
         return true;
}

echo $OUTPUT->header();

function obtenerFechaInicio() {
    // if (estuvoapagado()) { logica para obtener la ultima fecha en la que se puso asistencia en la materia }
    return '2024-05-27';
}

$start_date = '2024-05-27'; // fecha de ayer
$end_date = '2024-05-30';  

// Modificar la consulta SQL para filtrar por un rango de fechas y ordenar por fecha de inicio ascendente
$sql = "SELECT zd.*, z.name, z.course
FROM {zoom_meeting_details} zd
JOIN {zoom} z ON zd.zoomid = z.id
WHERE z.course = :courseid AND 
DATE(FROM_UNIXTIME(zd.start_time)) BETWEEN :start_date AND :end_date AND
DAYOFWEEK(FROM_UNIXTIME(zd.start_time)) IN (2, 3, 4, 5) AND
TIME(FROM_UNIXTIME(zd.start_time)) BETWEEN '18:30:00' AND '23:30:00'
ORDER BY zd.start_time ASC"; 

$params = [
    'start_date' => $start_date,
    'end_date' => $end_date,
    'courseid' => $course->id
];

$meetings = $DB->get_records_sql($sql, $params);
print_r($meetings);

echo "<br>";
echo "hasta aca sin filtrar";
echo "<br>";

$filteredMeetings = new stdClass();

// Filtrar las reuniones y unirlas en caso de que sean del mismo grupo (zoomid) y la misma fecha de reunión y obtener horario de inicio y duración total
foreach ($meetings as $meeting) {
    $id = $meeting->id;
    $zoomid = $meeting->zoomid;
    $meeting_id = $meeting->meeting_id;
    $start_time = $meeting->start_time;
    $end_time = $meeting->end_time;
    $duration = $meeting->duration;
    $topic = $meeting->topic;
    $name = $meeting->name;

    $dateTime = new DateTime("@$start_time");

    $dateTime->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));

    $fecha_reunion = $dateTime->format('Y-m-d');


    // Crear una clave única basada en zoomid y fecha de reunión
    $unique_key = $zoomid . '_' . $fecha_reunion;

    if (isset($filteredMeetings->$unique_key)) {
        // Sumar la duración
        $filteredMeetings->$unique_key->duration += $duration;

        // Actualizar el start_time al más temprano y el end_time al más tardío
        if ($start_time < $filteredMeetings->$unique_key->start_time) {
            $filteredMeetings->$unique_key->start_time = $start_time;
        }
        if ($end_time > $filteredMeetings->$unique_key->end_time) {
            $filteredMeetings->$unique_key->end_time = $end_time;
        }

        // Agregar el nuevo id al array de ids, ya que es la misma reunión pero en veces distintas
        $filteredMeetings->$unique_key->ids[] = $id;
    } else {
        // Si la reunión no existe, agregar un nuevo registro
        $filteredMeetings->$unique_key = (object)[
            'ids' => [$id],
            'zoomid' => $zoomid,
            'meeting_id' => $meeting_id,
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'duration' => $duration,
            'topic' => $topic,
            'fecha_reunion' => $fecha_reunion
        ];
    }
}

// Procesar cada reunión
foreach ($filteredMeetings as $unique_key => $meeting) { 
    echo "<br> Materia: " . $course->fullname . " Duración: " . $meeting->duration . " minutos\n" . "Fecha de reunión: " . $meeting->fecha_reunion . "<br>";


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
    
 $participantesXgroupid = [];

    foreach ($participants as $participant) {
        $email = $participant['user_email'];
        $name = $participant['name'];
        $duration = $participant['duration'];
        $join_time = $participant['join_time'];
        $userid = $participant['userid'];
    
        if ($userid != null) {  
            // Realizar la consulta para obtener el ID del grupo al que pertenece el participante
            $sql = "SELECT gm.groupid 
                    FROM {groups_members} gm 
                    INNER JOIN {groups} g ON gm.groupid = g.id 
                    INNER JOIN {user} u ON gm.userid = u.id 
                    INNER JOIN {course} c ON g.courseid = c.id 
                    WHERE u.id = :userid 
                    AND c.id = :courseid";
    
            // Ejecutar la consulta y obtener el ID del grupo
            $group_id = $DB->get_field_sql($sql, array('userid' => $userid, 'courseid' => $course->id));
    
            // Si el participante pertenece a un grupo
         if(!empty($group_id )) {
    if ($group_id  !== false) {
        // Si el grupo aún no existe en el array, lo inicializamos
        if (!isset($participantesXgroupid[$group_id])) {
            $participantesXgroupid[$group_id ] = [];
        }

        // Si el participante aún no existe en el array del grupo, lo inicializamos
        if (!isset($participantesXgroupid[$group_id ][$userid])) {
            $participantesXgroupid[$group_id][$userid] = [
                'join_time' => $join_time,
                'duration' => $duration,
                'name' => $name,
                'user_email' => $email,
                'userid'=> $userid
            ];
        } else {
            // Si el participante ya existe, actualizamos el join_time al más temprano
            if ($join_time < $participantesXgroupid[$group_id][$userid]['join_time']) {
                $participantesXgroupid[$group_id][$userid]['join_time'] = $join_time;
            }
            // Sumamos la duración al participante
            $participantesXgroupid[$group_id][$userid]['duration'] += $duration;
        }
    }
}
   }
    }
    
    // obtener la sesion de asistencia  
    foreach ($participantesXgroupid as $group_id => $participantes) {
        echo "<br> Grupo ID: $group_id \n <br>";
        
        try{
            $attendance = $DB->get_record('attendance', array('course' => $course->id), 'id', MUST_EXIST);
            $attendance_id = $attendance->id;
            $grupo = $DB->get_record('groups', array('id' => $group_id));
            $courseid = $course->id;
        
            $sql = "SELECT *
            FROM {attendance_sessions} a
            WHERE attendanceid IN (SELECT id FROM {attendance} WHERE course = :courseid) 
            AND groupid = :groupid
            AND DATE(FROM_UNIXTIME(a.sessdate)) = :test_date";
        
        
        $params = [
            'courseid' => $courseid,
            'groupid' => $group_id,
            'test_date' => $meeting->fecha_reunion
        ];
        
        $session = $DB->get_record_sql($sql, $params);
        
    if($session->id == null) {
        echo 'no se creo la sesion';
    
    // Datos de la sesión
    $attendanceid = $attendance_id; // Reemplaza con el ID de tu instancia de attendance
    $groupid = $group_id; // ID del grupo, 0 si es para todos los grupos
    $duration = $meeting->duration*60; // Duración en segundos
    $sessdate = $meeting->start_time; // Fecha y hora de la sesión (timestamp)
    $description = 'Sesion de Clase Normal'; // Descripción de la sesión
    
    // Llamar a la función para agregar la sesión
    add_attendance_session($attendanceid, $groupid, $duration, $sessdate, $description);
    
    $attendance = $DB->get_record('attendance', array('course' => $course->id), 'id', MUST_EXIST);
    $attendance_id = $attendance->id;
    $grupo = $DB->get_record('groups', array('id' => $group_id));
    $courseid = $course->id;

    $sql = "SELECT *
    FROM {attendance_sessions} a
    WHERE attendanceid IN (SELECT id FROM {attendance} WHERE course = :courseid) 
    AND groupid = :groupid
    AND DATE(FROM_UNIXTIME(a.sessdate)) = :test_date";


$params = [
    'courseid' => $courseid,
    'groupid' => $group_id,
    'test_date' => $meeting->fecha_reunion
];

$session = $DB->get_record_sql($sql, $params);

}
            
$userid = 2;

$comienzoReunion = $meeting->start_time;
$duracion = $meeting->duration;

$sessionid = $session->id;
$attendanceid = $session->attendanceid;

$statuses = $DB->get_records_sql("
                SELECT at.id
                FROM {attendance_statuses} AS at
                WHERE at.attendanceid = ?", array($attendanceid));
            
            // Inicializar una cadena para almacenar los IDs
            $statuses_string = '';
            
            // Recorrer los registros y concatenar los IDs de status 
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
            
            $status_tarde= $DB->get_field_select(
                'attendance_statuses',
                'id',
                'attendanceid = ? AND acronym = ? ',
                array($attendanceid, 'R')
            );
            
            $status_presente = $DB->get_field_select(
                'attendance_statuses',
                'id',
                'attendanceid = ? AND acronym = ? ',
                array($attendanceid, 'P')
            );
            
                        echo "<br> Grupo ID: $group_id \n" . 'nombre del grupo ' . $grupo->name . 'ID de la session: ' .$sessionid; '<br>';
            
                        $cursantes = groups_get_members($group_id); 
            
                        echo "cursantes:\n" . '<br>';
            
                        
echo 'fin' . '<br><br><br><br>'; 

$actualizacion = [];
                             
            foreach ($cursantes as $key => $value) {
               
                $cursante_id = $value->id;
                echo ' nombre:' . $value->firstname . ''. $value->lastname .'<br>'; 
                // Verificar si el ID del cursante está en la lista de participantes de la reunion 

                // Obtener la información completa del participante
                    $participante = $participantesXgroupid[$group_id][$cursante_id];
                    
                    if(estuvoTiempoRequerido($participante['duration'] , $porcRequerido, $duracion))  {
                        echo 'paso por estuvo tiempo requerido'; 
                    if(!llegoTarde($participante, $comienzoReunion)) {
                        echo 'paso por llegada tarde'; 
                        $cursantePresente = true;
                         // aca habria que pasar los datos a las tablas de attendance, sessionid de attendance se podria obtener con el grupo y la fecha de la reunion 
                          echo "El cursante {$value->firstname} {$value->lastname}, con el correo {$value->email} asistió a la reunión y estuvo el tiempo requerido por el presente. duracion = {$participante['duration']} segundos";
                           echo "<br>";
                           
                           $actualizacion = array(
                            'sessionid' => $sessionid,'studentid' => $value->id,'takenbyid' => $userid,'statusid' => $status_presente,'statusset' => $statuses_string
                            //'remark' => 'ORT ASSISTANCE BOT'
                        );
                        $actualizaciones[] = $actualizacion;
                    
                    }
                    else {
                          echo "El cursante  {$value->firstname} {$value->lastname}, con el correo {$value->email} asistió a la reunión pero llego tarde y estuvo el tiempo requerido por el presente. {duracion = {$participante['duration']} segundos";
                           echo "<br>";
                           $actualizacion = array('sessionid' => $sessionid,'studentid' => $value->id,'takenbyid' => $userid,'statusid' => $status_tarde,'statusset' => $statuses_string
                        //'remark' => 'ORT ASSISTANCE BOT'
                        );
                        $actualizaciones[] = $actualizacion;
                    }
                 } else { 
                    
                    echo "El cursante  {$value->firstname} {$value->lastname}, con el correo {$value->email} no asistió a la reunión.";
                    echo "<br>";
             
                    $actualizacion = array(
                        'sessionid' => $sessionid,'studentid' => $value->id,'takenbyid' => $userid,'statusid' => $status_ausente,'statusset' => $statuses_string//'remark' => 'ORT ASSISTANCE BOT'
                    );
                    $actualizaciones[] = $actualizacion;
                 }

            } foreach ($actualizaciones as $actualizacion) {
                
                update_user_status($actualizacion['sessionid'], $actualizacion['studentid'], $actualizacion['takenbyid'], $actualizacion['statusid'], $actualizacion['statusset']);
                echo '<br>';
                echo 'taking assistance: DONE';
            } 
} catch (error) {} 
    }
}

  // && strcasecmp($result['user_email'], $cursante->email) == 0  lo estoy sacando por que no todos los participantes tienen el email

echo $OUTPUT->footer();


