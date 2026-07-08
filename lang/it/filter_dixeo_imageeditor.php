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

$string['filtername'] = 'Editor immagini Dixeo';
$string['pluginname'] = 'Editor immagini Dixeo';
$string['privacy:metadata'] = 'Il filtro editor immagini Dixeo memorizza metadati della cronologia versioni e byte di immagini archiviate per immagini di contenuto incorporate.';
$string['dixeo_imageeditor:edit'] = 'Modificare immagini di contenuto incorporate con IA';
$string['setting_enabled'] = 'Abilita editor immagini Dixeo';
$string['setting_enabled_desc'] = 'Se disabilitato, il filtro non inserisce controlli di modifica.';
$string['editimage'] = 'Modifica immagine';
$string['modal_title'] = 'Editor immagini Dixeo';
$string['mode_new_image'] = 'Nuova immagine';
$string['mode_edit_current_image'] = 'Modifica attuale';
$string['prompt_label_generate'] = 'Descrivi l\'immagine che vuoi che l\'IA crei';
$string['prompt_placeholder_generate'] = 'Prova \'Paesaggio montano\'';
$string['prompt_label_edit'] = 'Descrivi le modifiche da apportare all\'immagine';
$string['prompt_placeholder_edit'] = 'es. Rimuovi il portatile dalla scrivania, ingrandisci leggermente e mantieni la stessa illuminazione.';
$string['prompt_required'] = 'Inserisci una descrizione prima di continuare.';
$string['instructions_required'] = 'Descrivi le modifiche da applicare.';
$string['shape_label'] = 'Formato immagine';
$string['shape_landscape'] = 'Orizzontale';
$string['shape_square'] = 'Quadrato';
$string['shape_portrait'] = 'Verticale';
$string['quality_label'] = 'Qualità';
$string['quality_low'] = 'Bassa';
$string['quality_medium'] = 'Media';
$string['quality_high'] = 'Alta';
$string['history_label'] = 'Cronologia versioni';
$string['history_set_current'] = 'Imposta come immagine corrente';
$string['history_delete'] = 'Elimina dalla cronologia';
$string['history_actions'] = 'Azioni versione';
$string['submit_generate'] = 'Genera';
$string['submit_edit'] = 'Modifica';
$string['generating_status'] = 'Generazione immagine...';
$string['error_job_failed'] = 'Generazione immagine non riuscita. Riprova.';
$string['error_locked'] = 'È già in corso un job immagine per questa immagine.';
$string['error_not_eligible'] = 'Questa immagine non può essere modificata.';
$string['error_revert_blocked'] = 'Impossibile ripristinare mentre un job è in corso.';
$string['error_delete_blocked'] = 'Impossibile eliminare dalla cronologia mentre un job è in corso.';
$string['error_delete_current'] = 'Impossibile eliminare la voce della cronologia corrispondente all\'immagine corrente.';
$string['privacy:metadata:usermodified'] = 'L\'utente che ha creato una voce della cronologia versioni.';
$string['privacy:metadata:timecreated'] = 'Quando è stata creata la voce della cronologia versioni.';
$string['privacy:metadata:versiontable'] = 'Memorizza metadati sulle versioni archiviate delle immagini per contenuto incorporato.';
$string['privacy:metadata:filename'] = 'Nome file dell\'immagine incorporata.';
$string['privacy:metadata:source'] = 'Come è stata creata la versione archiviata.';
$string['privacy:metadata:historyfiles'] = 'Le copie archiviate delle versioni precedenti delle immagini sono memorizzate nel file system.';
$string['privacy:pathversions'] = 'Cronologia versioni immagine';
$string['task_cleanup_version_history'] = 'Pulizia della cronologia delle versioni delle immagini Dixeo';
$string['close'] = 'Chiudi';
$string['history_preview_title'] = 'Anteprima versione';
$string['history_preview_prev'] = 'Versione precedente';
$string['history_preview_next'] = 'Versione successiva';
$string['upload_image'] = 'Carica';
$string['upload_replace_title'] = 'Sostituire l\'immagine corrente?';
$string['upload_replace_body'] = 'Il file selezionato sostituirà l\'immagine corrente. La versione precedente verrà salvata nella cronologia versioni.';
$string['upload_replace_confirm'] = 'Sostituisci immagine';
$string['upload_invalid_type'] = 'Scegli un file immagine supportato ({$a}).';
$string['error_upload_blocked'] = 'Impossibile caricare mentre un job immagine IA è in corso.';
$string['error_upload_invalid_image'] = 'Il file caricato non è un\'immagine supportata.';
$string['manual_edit_start'] = 'Modifica immagine manualmente';
$string['manual_save'] = 'Salva';
$string['manual_discard'] = 'Annulla';
$string['manual_download'] = 'Scarica';
$string['manual_rotate_clockwise'] = 'Ruota';
$string['manual_apply_crop'] = 'Ritaglia';
$string['manual_flip_horizontal'] = 'Capovolgi orizzontalmente';
$string['manual_flip_vertical'] = 'Capovolgi verticalmente';
$string['manual_zoom_in'] = 'Ingrandisci';
$string['manual_zoom_out'] = 'Riduci';
$string['manual_zoom_reset'] = 'Reimposta zoom';
$string['manual_filter_grayscale'] = 'Bianco e nero';
$string['manual_filter_sepia'] = 'Seppia';
$string['manual_brightness'] = 'Luminosità';
$string['manual_contrast'] = 'Contrasto';
$string['manual_toolbar_history'] = 'Cronologia ed esportazione';
$string['manual_toolbar_adjust'] = 'Regola immagine';
$string['manual_toolbar_zoom'] = 'Zoom';
$string['manual_undo'] = 'Annulla';
$string['manual_redo'] = 'Ripeti';
$string['manual_unsaved_changes_title'] = 'Annullare le modifiche non salvate?';
$string['manual_unsaved_changes_body'] = 'Hai modifiche manuali non salvate. Se esci ora, andranno perse.';
$string['error_manual_blocked'] = 'Impossibile salvare modifiche manuali mentre un job immagine IA è in corso.';
$string['error_manual_invalid_image'] = 'L\'immagine modificata non può essere elaborata.';
