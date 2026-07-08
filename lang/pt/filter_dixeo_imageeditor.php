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

$string['filtername'] = 'Editor de imagens Dixeo';
$string['pluginname'] = 'Editor de imagens Dixeo';
$string['privacy:metadata'] = 'O filtro editor de imagens Dixeo armazena metadados do histórico de versões e bytes de imagem arquivados para imagens de conteúdo incorporadas.';
$string['dixeo_imageeditor:edit'] = 'Editar imagens de conteúdo incorporadas com IA';
$string['setting_enabled'] = 'Ativar editor de imagens Dixeo';
$string['setting_enabled_desc'] = 'Quando desativado, o filtro não injeta controlos de edição.';
$string['editimage'] = 'Editar imagem';
$string['modal_title'] = 'Editor de imagens Dixeo';
$string['mode_new_image'] = 'Nova imagem';
$string['mode_edit_current_image'] = 'Editar atual';
$string['prompt_label_generate'] = 'Descreva a imagem que pretende que a IA crie';
$string['prompt_placeholder_generate'] = 'Experimente \'Paisagem montanhosa\'';
$string['prompt_label_edit'] = 'Descreva as alterações a fazer na imagem';
$string['prompt_placeholder_edit'] = 'p. ex. Remover o portátil da secretária, ampliar ligeiramente e manter a mesma iluminação.';
$string['prompt_required'] = 'Introduza uma descrição antes de continuar.';
$string['instructions_required'] = 'Descreva as alterações a aplicar.';
$string['shape_label'] = 'Formato da imagem';
$string['shape_landscape'] = 'Paisagem';
$string['shape_square'] = 'Quadrado';
$string['shape_portrait'] = 'Retrato';
$string['quality_label'] = 'Qualidade';
$string['quality_low'] = 'Baixa';
$string['quality_medium'] = 'Média';
$string['quality_high'] = 'Alta';
$string['history_label'] = 'Histórico de versões';
$string['history_set_current'] = 'Definir como imagem atual';
$string['history_delete'] = 'Eliminar do histórico';
$string['history_actions'] = 'Ações da versão';
$string['submit_generate'] = 'Gerar';
$string['submit_edit'] = 'Editar';
$string['generating_status'] = 'A gerar imagem...';
$string['error_job_failed'] = 'A geração da imagem falhou. Tente novamente.';
$string['error_locked'] = 'Já existe uma tarefa de imagem em curso para esta imagem.';
$string['error_not_eligible'] = 'Esta imagem não pode ser editada.';
$string['error_revert_blocked'] = 'Não é possível reverter enquanto uma tarefa está em curso.';
$string['error_delete_blocked'] = 'Não é possível eliminar do histórico enquanto uma tarefa está em curso.';
$string['error_delete_current'] = 'Não é possível eliminar a entrada do histórico que corresponde à imagem atual.';
$string['privacy:metadata:usermodified'] = 'O utilizador que criou uma entrada do histórico de versões.';
$string['privacy:metadata:timecreated'] = 'Quando a entrada do histórico de versões foi criada.';
$string['privacy:metadata:versiontable'] = 'Armazena metadados sobre versões arquivadas de imagens para conteúdo incorporado.';
$string['privacy:metadata:filename'] = 'Nome do ficheiro da imagem incorporada.';
$string['privacy:metadata:source'] = 'Como a versão arquivada foi criada.';
$string['privacy:metadata:historyfiles'] = 'As cópias arquivadas de versões anteriores das imagens são armazenadas no sistema de ficheiros.';
$string['privacy:pathversions'] = 'Histórico de versões da imagem';
$string['task_cleanup_version_history'] = 'Limpar o histórico de versões de imagens Dixeo';
$string['close'] = 'Fechar';
$string['history_preview_title'] = 'Pré-visualização da versão';
$string['history_preview_prev'] = 'Versão anterior';
$string['history_preview_next'] = 'Versão seguinte';
$string['upload_image'] = 'Carregar';
$string['upload_replace_title'] = 'Substituir a imagem atual?';
$string['upload_replace_body'] = 'O ficheiro selecionado substituirá a imagem atual. A versão anterior será guardada no histórico de versões.';
$string['upload_replace_confirm'] = 'Substituir imagem';
$string['upload_invalid_type'] = 'Escolha um ficheiro de imagem suportado ({$a}).';
$string['error_upload_blocked'] = 'Não é possível carregar enquanto uma tarefa de imagem por IA está em curso.';
$string['error_upload_invalid_image'] = 'O ficheiro carregado não é uma imagem suportada.';
$string['manual_edit_start'] = 'Editar imagem manualmente';
$string['manual_save'] = 'Guardar';
$string['manual_discard'] = 'Descartar';
$string['manual_download'] = 'Transferir';
$string['manual_rotate_clockwise'] = 'Rodar';
$string['manual_apply_crop'] = 'Recortar';
$string['manual_flip_horizontal'] = 'Inverter horizontalmente';
$string['manual_flip_vertical'] = 'Inverter verticalmente';
$string['manual_zoom_in'] = 'Ampliar';
$string['manual_zoom_out'] = 'Reduzir';
$string['manual_zoom_reset'] = 'Repor zoom';
$string['manual_filter_grayscale'] = 'Preto e branco';
$string['manual_filter_sepia'] = 'Sépia';
$string['manual_brightness'] = 'Brilho';
$string['manual_contrast'] = 'Contraste';
$string['manual_toolbar_history'] = 'Histórico e exportação';
$string['manual_toolbar_adjust'] = 'Ajustar imagem';
$string['manual_toolbar_zoom'] = 'Zoom';
$string['manual_undo'] = 'Anular';
$string['manual_redo'] = 'Refazer';
$string['manual_unsaved_changes_title'] = 'Descartar alterações não guardadas?';
$string['manual_unsaved_changes_body'] = 'Tem edições manuais não guardadas. Se sair agora, essas alterações serão perdidas.';
$string['error_manual_blocked'] = 'Não é possível guardar edições manuais enquanto uma tarefa de imagem por IA está em curso.';
$string['error_manual_invalid_image'] = 'A imagem editada não pôde ser processada.';
