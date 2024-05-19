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
$meeting_id = 167509144;
$details_id = 1;

$nombre_curso_zoom = $DB->get_field('zoom_meeting_details', 'topic', ['id' => $details_id]); // este seria el nombre de la materia 
//$nombre_reunion_zoom = $DB->get_field('zoom', 'name', ['meeting_id' => $meeting_id]); // este es el nombre de la reunion (curso + grupo)

$duracion_reunion = $DB->get_field('zoom_meeting_details', 'duration', ['id' => $details_id]);

$hora_comienzo_reunion = $DB->get_field('zoom_meeting_details', 'start_time', ['id' => $details_id]);

const PORC_TIEMPO_REQUERIDO = 0.75;


//obtener participantes de la reunion
$participants = $DB->get_records_sql(" 
    SELECT zmp.name, zmp.user_email, zmp.join_time, zmp.leave_time, zmp.duration
    FROM {zoom_meeting_participants} zmp
    JOIN {zoom_meeting_details} zmd ON zmp.detailsid = zmd.id
    JOIN {zoom} z ON zmd.meeting_id = z.meeting_id
    WHERE zmd.meeting_id = ? AND zmp.detailsid = ?
", array($meeting_id, $details_id));

$participants = json_decode( json_encode($participants), true);

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

function llegoTarde($participant) {
    global $hora_comienzo_reunion;
    return (($participant['join_time'] / 60) - ($hora_comienzo_reunion / 60)) > 20;
}

function estuvoTiempoRequerido($total_duracion, $porcentaje) {   // Investigar casos de uso !
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
echo "El nombre de la reunión de zoom y este curso: ";
echo "<br>";
try {
    if ($nombre_curso_zoom == $course->fullname) { 
        echo "¡Coincide!, ambos son: " . ' ' . $nombre_curso_zoom;
        echo "<br>";
        echo 'La duración fue de ' ."". $duracion_reunion ." " ."minutos";
        echo "<br>";
        foreach ($cursantes as $cursante) {
         $cursanteEncontrado = false;
         $cursantePresente = false;
         $total_duracion = 0;
         $fullname = $cursante->lastname . ' ' . $cursante->firstname;
        foreach ($participants as $participant) {
            
        if ($participant['name'] == $fullname && $participant['user_email'] == $cursante->email) {
            $cursanteEncontrado = true;
            $total_duracion += $participant['duration'];
      if(estuvoTiempoRequerido($total_duracion, PORC_TIEMPO_REQUERIDO))  { 
        if(!llegoTarde($participant)) { 
            $cursantePresente = true;
            $contador += 1;
             echo $contador ."". ")" ;
             // aca habria que pasar los datos a las tablas de attendance, sessionid de attendance se podria obtener con el grupo y la fecha de la reunion 
              echo "El cursante {$fullname}, con el correo {$cursante->email} asistió a la reunión y estuvo el tiempo requerido por el presente.";
               echo "<br>";
              break;
        }
        else {
            $cursantePresente = true;
            $contador += 1;
             echo $contador ."". ")" ;
              echo "El cursante {$fullname}, con el correo {$cursante->email} asistió a la reunión pero llego tarde y estuvo el tiempo requerido por el presente.";
               echo "<br>";
              break;
        }
     }
            }  
        } 
        if (!$cursanteEncontrado|| !$cursantePresente) { 
            $contador += 1;
            echo $contador ."". ")" ;
            echo "El cursante {$fullname}, con el correo {$cursante->email} no asistió a la reunión ";
            echo "<br>"; }
        } } 
        else {  
          echo "No coinciden ";
        }
} catch (\Error $e) {
    //throw $th;
}

echo $OUTPUT->footer();