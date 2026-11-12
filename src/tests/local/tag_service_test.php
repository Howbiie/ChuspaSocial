<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License
// as published by the Free Software Foundation, either version 3
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Unit tests for tag_service::get_feed_by_tag().
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

use advanced_testcase;

/**
 * Pruebas unitarias para tag_service::get_feed_by_tag().
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\tag_service::get_feed_by_tag
 */
class tag_service_test extends advanced_testcase {

    /**
     * Configuración previa a cada test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Crea una publicación de prueba.
     *
     * @param int $userid Autor de la publicación.
     * @param int $courseid Curso de la publicación.
     * @return int ID de la publicación.
     */
    private function create_test_post(int $userid, int $courseid): int {
        global $DB;

        return (int) $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Test post',
            'contentformat' => FORMAT_HTML,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Asigna etiquetas a una publicación.
     *
     * @param int $postid ID de la publicación.
     * @param int $courseid ID del curso.
     * @param array $tags Etiquetas que se asignarán.
     */
    private function tag_test_post(int $postid, int $courseid, array $tags): void {
        $context = $courseid > 0
            ? \context_course::instance($courseid)
            : \context_system::instance();

        \core_tag_tag::set_item_tags(
            'local_chuspasocial',
            'local_chuspasocial_post',
            $postid,
            $context,
            $tags
        );
    }

    /**
     * Comprueba que se devuelvan únicamente publicaciones con la etiqueta fisica.
     */
    public function test_get_feed_by_tag_filtra_fisica(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $fisicapostid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($fisicapostid, $course->id, ['fisica']);

        $matematicaspostid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($matematicaspostid, $course->id, ['matematicas']);

        $this->setUser($user);

        $result = tag_service::get_feed_by_tag('fisica', 0, 10);

        $this->assertCount(1, $result);
        $this->assertEquals($fisicapostid, $result[0]->id);
    }

    /**
     * Comprueba que no se devuelvan publicaciones de cursos ajenos.
     */
    public function test_get_feed_by_tag_excluye_cursos_ajenos(): void {
        $generator = $this->getDataGenerator();

        $mycourse = $generator->create_course();
        $othercourse = $generator->create_course();

        $user = $generator->create_user();
        $otheruser = $generator->create_user();

        $generator->enrol_user($user->id, $mycourse->id, 'student');
        $generator->enrol_user($otheruser->id, $othercourse->id, 'student');

        $mypostid = $this->create_test_post($user->id, $mycourse->id);
        $this->tag_test_post($mypostid, $mycourse->id, ['fisica']);

        $otherpostid = $this->create_test_post($otheruser->id, $othercourse->id);
        $this->tag_test_post($otherpostid, $othercourse->id, ['fisica']);

        $this->setUser($user);

        $result = tag_service::get_feed_by_tag('fisica', 0, 10);

        $this->assertCount(1, $result);
        $this->assertEquals($mypostid, $result[0]->id);
    }

    /**
     * Comprueba que una etiqueta inexistente devuelva un array vacío.
     */
    public function test_get_feed_by_tag_etiqueta_inexistente(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($postid, $course->id, ['quimica']);

        $this->setUser($user);

        $result = tag_service::get_feed_by_tag('tag_inexistente_1163', 0, 10);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
