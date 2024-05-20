<?php
/**
 * Prints an instance of mod_asistbotv1.
 *
 * @package     mod_asistbotv1
 * @copyright   2024 Your Name <abich@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require('C:\xampp\htdocs\moodle\mod\attendance\classes\attendance_webservices_handler.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$a = optional_param('a', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('asistbotv1', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('asistbotv1', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('asistbotv1', array('id' => $a), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('asistbotv1', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/asistbotv1/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// aca el meeting y details lo estoy hardcodeando para que coincida con el curso que arme, pero lo obtendria directamente de la reunion de zoom 
// 
$meeting_id = 167509144 ;
$details_id = 1;

$nombre_curso_zoom = $DB->get_field('zoom_meeting_details', 'topic', ['id' => $details_id]); // este seria el nombre de la materia 
//$nombre_reunion_zoom = $DB->get_field('zoom', 'name', ['meeting_id' => $meeting_id]); // este es el nombre de la reunion (curso y grupo)

$duracion_reunion = $DB->get_field('zoom_meeting_details', 'duration', ['id' => $details_id]);

$hora_comienzo_reunion = $DB->get_field('zoom_meeting_details', 'start_time', ['id' => $details_id]);

const PORC_TIEMPO_REQUERIDO = 0.75;

echo $OUTPUT->header();
//obtener participantes de la reunion
$participants = $DB->get_records_select(
    'zoom_meeting_participants', // Tabla
    'detailsid = ?',             // Condición WHERE
    array($details_id),          // Parámetros para la condición
    '',                          // Ordenamiento (vacío en este caso)
    'id, name, duration, join_time, leave_time, user_email' // Campos seleccionados
);

print_r($participants);

$participants = json_decode( json_encode($participants), true);

$results = [];

// Procesar cada participante
  
$results = []; // Inicializar el array $results

foreach ($participants as $participant) {
    $email = $participant['user_email'];
    $name = $participant['name'];
    $duration = $participant['duration'];
    $join_time = $participant['join_time'];

    // Si el email ya existe en el array resultante, sumar la duración y tomar el join_time más temprano
    if (isset($results[$email])) {
        // Sumar la duración
        $results[$email]['duration'] += $duration;

        // Actualizar el join_time al más temprano
        if ($join_time < $results[$email]['join_time']) {
            $results[$email]['join_time'] = $join_time;
        }
    } else {
        // Si el email no existe, agregar un nuevo registro
        $results[$email] = [
            'join_time' => $join_time,
            'name' => $name,
            'user_email' => $email,
            'duration' => $duration
        ];
    }
}

// Ahora el array $results contiene la duración total para cada email y el join_time más temprano

echo 'resultados' . '<br>' ;
print_r($results);

//obtener cursantes
try {
    $context = context_course::instance($course->id); 
     // si la reunion esta asignada a un grupo especifico, podria obtener los cursantes de ese grupo  
     // $groupname = "YA-TP1A"; o directamente tomar el curso al grupo que esta asignada la reunion
       // $group = $DB->get_record('groups', array('name' => $groupname, 'courseid' => $course->id));
       // if ($group) {
        //    $cursantes = groups_get_members($group->id);
    $cursantes = get_enrolled_users($context);
} catch (\Throwable $th) {

}

function llegoTarde($result) {
    global $hora_comienzo_reunion;
    return (($result['join_time'] / 60) - ($hora_comienzo_reunion / 60)) > 20;
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



foreach ($participants as $participant) { 
echo 'nombre: ' . $participant['name'] . ' duracion: ' . $participant['duration'] ;
}

$userid = 2; // aca va el id del usuario admin
$courses = attendance_handler::get_courses_with_today_sessions($userid);


// Iterar sobre los cursos y sus instancias de asistencia
$nombreCurso = $course->fullname;
$grupo = 0; // no hay grupos
$sessionHoy;
$sessionidHoy = 0;


foreach ($courses as $course) {
    echo "Curso: {$course->fullname}<br>";
    foreach ($course->attendance_instances as $instance) {
        echo "Instancia de asistencia: {$instance['name']}<br>";
        foreach ($instance['today_sessions'] as $session) {
            echo "Sesión de hoy: {$session->id}<br>";
            echo "id grupo de la sesion: {$session->groupid}<br>";
            if($course->fullname == $nombreCurso && $session->groupid == $grupo) {
              $sessionidHoy = $session->id;
              $sessionHoy = $session;
        }
    } }
}
echo "mi sesion es id {$sessionidHoy}<br>";

$attendanceid = $sessionHoy->attendanceid;
echo "$attendanceid";

// Obtener registros de la base de datos
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


    echo 'hasta aca part' . '<br>';
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
echo $status_presente. ' <br>';
echo "El nombre de la reunión de zoom y este curso: ";
echo "<br>";
try {
    if ($nombre_curso_zoom == $nombreCurso) { 
        echo "¡Coincide!, ambos son: " . ' ' . $nombre_curso_zoom;
        echo "<br>";
        echo 'La duración fue de ' ."". $duracion_reunion ." " ."minutos";
        echo "<br>";
      
        foreach ($cursantes as $cursante) {
         $cursanteEncontrado = false;
         $cursantePresente = false;


         $fullname = trim($cursante->lastname . ' ' . $cursante->firstname);
         $fullname2 = trim($cursante->firstname . ' ' . $cursante->lastname);
         
        foreach ($results as $result) {
            
              if ((strcasecmp($result['name'], $fullname) == 0 || strcasecmp($result['name'], $fullname2) == 0) &&
        strcasecmp($result['user_email'], $cursante->email) == 0) {
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
                'sessionid' => $sessionHoy->id,
                'studentid' => $cursante->id,
                'takenbyid' => $userid,
                'statusid' => $status_presente,
                'statusset' => $statuses_string
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
               $actualizacion = array(
                'sessionid' => $sessionHoy->id,
                'studentid' => $cursante->id,
                'takenbyid' => $userid,
                'statusid' => $status_tarde,
                'statusset' => $statuses_string
            
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
                'sessionid' => $sessionHoy->id,
                'studentid' => $cursante->id,
                'takenbyid' => $userid,
                'statusid' => $status_ausente,
                'statusset' => $statuses_string
            );
            $actualizaciones[] = $actualizacion;
        }
           
        } } 
        else {  
          echo "No coinciden ";
        }
} catch (\Error $e) {
    //throw $th;
}

if($courses && !empty($sessionHoy)) {
foreach ($actualizaciones as $actualizacion) {
    attendance_handler::update_user_status($actualizacion['sessionid'], $actualizacion['studentid'], $actualizacion['takenbyid'], $actualizacion['statusid'], $actualizacion['statusset']);
} }

echo $OUTPUT->footer();