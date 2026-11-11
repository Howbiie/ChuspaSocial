<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tag page of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$tag = required_param('tag', PARAM_TAG);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chuspasocial/tag.php', ['tag' => $tag]));
$PAGE->set_title($tag);
$PAGE->set_heading(s($tag));


$page = 0;
$perpage = 20;

// Obtener las publicaciones visibles de la etiqueta.
$records = \local_chuspasocial\local\tag_service::get_feed_by_tag(
    $tag,
    $page,
    $perpage
);

// Obtener los datos de reacciones en lote.
$postids = array_map(
    static fn($record) => (int) $record->id,
    $records
);

$counts = \local_chuspasocial\local\reaction_service::count_for_posts($postids);
$mine = \local_chuspasocial\local\reaction_service::has_reacted_many($postids);

// Preparar las publicaciones para la plantilla.
$posts = [];

foreach ($records as $record) {
    $record = ($record instanceof \core\persistent)
        ? $record->to_record()
        : (object) $record;

    $author = \core_user::get_user($record->userid);

    if (!$author) {
        continue;
    }

    $postcontext = ((int) $record->courseid > 0)
        ? \context_course::instance((int) $record->courseid)
        : $context;

    $exporter = new \local_chuspasocial\external\post_exporter(
        $record,
        [
            'context' => $postcontext,
            'author' => $author,
            'reactions' => (int) ($counts[$record->id] ?? 0),
            'hasreacted' => (bool) ($mine[$record->id] ?? false),
        ]
    );

    $data = (array) $exporter->export($OUTPUT);

    $data['postid'] = (int) $record->id;
    $data['content'] = format_text(
        $data['content'],
        $data['contentformat'],
        ['context' => $postcontext]
    );

    $data['authorname'] = $data['authorfullname'];
    $data['isannouncement'] = (
        $record->type ===
        \local_chuspasocial\local\persistent\post::TYPE_ANNOUNCEMENT
    );

    $posts[] = $data;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_chuspasocial/wall', ['posts' => $posts]);
echo $OUTPUT->footer();