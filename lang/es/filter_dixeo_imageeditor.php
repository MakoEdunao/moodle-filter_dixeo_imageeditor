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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['filtername'] = 'Editor de imágenes Dixeo';
$string['pluginname'] = 'Editor de imágenes Dixeo';
$string['privacy:metadata'] = 'El filtro editor de imágenes Dixeo almacena metadatos del historial de versiones y bytes de imagen archivados para imágenes de contenido incrustadas.';
$string['dixeo_imageeditor:edit'] = 'Editar imágenes de contenido incrustadas con IA';
$string['setting_enabled'] = 'Activar editor de imágenes Dixeo';
$string['setting_enabled_desc'] = 'Si está desactivado, el filtro no inyecta controles de edición.';
$string['editimage'] = 'Editar imagen';
$string['modal_title'] = 'Editor de imágenes Dixeo';
$string['mode_new_image'] = 'Imagen nueva';
$string['mode_edit_current_image'] = 'Editar actual';
$string['prompt_label_generate'] = 'Describa la imagen que desea que cree la IA';
$string['prompt_placeholder_generate'] = 'Pruebe \'Paisaje montañoso\'';
$string['prompt_label_edit'] = 'Describa los cambios que desea hacer en la imagen';
$string['prompt_placeholder_edit'] = 'p. ej. Quitar el portátil del escritorio, acercar ligeramente y mantener la misma iluminación.';
$string['prompt_required'] = 'Introduzca una descripción antes de continuar.';
$string['instructions_required'] = 'Describa los cambios que desea aplicar.';
$string['shape_label'] = 'Formato de imagen';
$string['shape_landscape'] = 'Horizontal';
$string['shape_square'] = 'Cuadrado';
$string['shape_portrait'] = 'Vertical';
$string['quality_label'] = 'Calidad';
$string['quality_low'] = 'Baja';
$string['quality_medium'] = 'Media';
$string['quality_high'] = 'Alta';
$string['history_label'] = 'Historial de versiones';
$string['history_set_current'] = 'Establecer como imagen actual';
$string['history_delete'] = 'Eliminar del historial';
$string['history_actions'] = 'Acciones de versión';
$string['submit_generate'] = 'Generar';
$string['submit_edit'] = 'Editar';
$string['generating_status'] = 'Generando imagen...';
$string['error_job_failed'] = 'Error al generar la imagen. Inténtelo de nuevo.';
$string['error_locked'] = 'Ya hay una tarea de imagen en curso para esta imagen.';
$string['error_not_eligible'] = 'Esta imagen no se puede editar.';
$string['error_revert_blocked'] = 'No se puede revertir mientras hay una tarea en curso.';
$string['error_delete_blocked'] = 'No se puede eliminar del historial mientras hay una tarea en curso.';
$string['error_delete_current'] = 'No se puede eliminar la entrada del historial que coincide con la imagen actual.';
$string['privacy:metadata:usermodified'] = 'El usuario que creó una entrada del historial de versiones.';
$string['privacy:metadata:timecreated'] = 'Cuándo se creó la entrada del historial de versiones.';
$string['privacy:metadata:versiontable'] = 'Almacena metadatos sobre versiones archivadas de imágenes para contenido incrustado.';
$string['privacy:metadata:filename'] = 'Nombre del archivo de la imagen incrustada.';
$string['privacy:metadata:source'] = 'Cómo se creó la versión archivada.';
$string['privacy:metadata:historyfiles'] = 'Las copias archivadas de versiones anteriores de imágenes se almacenan en el sistema de archivos.';
$string['privacy:pathversions'] = 'Historial de versiones de imagen';
$string['task_cleanup_version_history'] = 'Limpiar el historial de versiones de imágenes de Dixeo';
$string['close'] = 'Cerrar';
$string['history_preview_title'] = 'Vista previa de versión';
$string['history_preview_prev'] = 'Versión anterior';
$string['history_preview_next'] = 'Versión siguiente';
$string['upload_image'] = 'Subir';
$string['upload_replace_title'] = '¿Reemplazar la imagen actual?';
$string['upload_replace_body'] = 'El archivo seleccionado reemplazará la imagen actual. La versión anterior se guardará en el historial de versiones.';
$string['upload_replace_confirm'] = 'Reemplazar imagen';
$string['upload_invalid_type'] = 'Elija un archivo de imagen compatible ({$a}).';
$string['error_upload_blocked'] = 'No se puede subir mientras hay una tarea de imagen por IA en curso.';
$string['error_upload_invalid_image'] = 'El archivo subido no es una imagen compatible.';
$string['manual_edit_start'] = 'Editar imagen manualmente';
$string['manual_save'] = 'Guardar';
$string['manual_discard'] = 'Descartar';
$string['manual_download'] = 'Descargar';
$string['manual_rotate_clockwise'] = 'Rotar';
$string['manual_apply_crop'] = 'Recortar';
$string['manual_flip_horizontal'] = 'Voltear horizontalmente';
$string['manual_flip_vertical'] = 'Voltear verticalmente';
$string['manual_zoom_in'] = 'Acercar';
$string['manual_zoom_out'] = 'Alejar';
$string['manual_zoom_reset'] = 'Restablecer zoom';
$string['manual_filter_grayscale'] = 'Blanco y negro';
$string['manual_filter_sepia'] = 'Sepia';
$string['manual_brightness'] = 'Brillo';
$string['manual_contrast'] = 'Contraste';
$string['manual_toolbar_history'] = 'Historial y exportación';
$string['manual_toolbar_adjust'] = 'Ajustar imagen';
$string['manual_toolbar_zoom'] = 'Zoom';
$string['manual_undo'] = 'Deshacer';
$string['manual_redo'] = 'Rehacer';
$string['manual_unsaved_changes_title'] = '¿Descartar cambios no guardados?';
$string['manual_unsaved_changes_body'] = 'Tiene ediciones manuales sin guardar. Si sale ahora, se perderán esos cambios.';
$string['error_manual_blocked'] = 'No se pueden guardar ediciones manuales mientras hay una tarea de imagen por IA en curso.';
$string['error_manual_invalid_image'] = 'No se pudo procesar la imagen editada.';
