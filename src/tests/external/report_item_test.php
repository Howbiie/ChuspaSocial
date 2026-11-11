
<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace local_chuspasocial\external;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use context_course;
use context_system;
use core_external\external_api;

/**
 * Tests for the report_item external function.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\report_item
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_item_test extends advanced_testcase {

    /**
     * Creates a post directly in the database.
     *
     * @param int $userid Author user id.
     * @param int $courseid Course id.
     * @return int Post id.
     */
    private function create_post(int $userid, int $courseid): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Post de prueba',
            'contentformat' => FORMAT_PLAIN,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Allows students to report content at system context.
     *
     * @param int $permission Permission value.
     */
    private function set_report_permission(int $permission): void {
        $roles = get_archetype_roles('student');
        $studentrole = reset($roles);

        role_change_permission(
            $studentrole->id,
            context_system::instance(),
            'local/chuspasocial:report',
            $permission
        );
    }

    /**
     * A valid external call creates a report.
     */
    public function test_execute_valid_call(): void {
        global $DB;

        $this->resetAfterTest();

        $this->set_report_permission(CAP_ALLOW);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $author = $generator->create_user();
        $reporter = $generator->create_user();

        $generator->enrol_user($author->id, $course->id, 'student');
        $generator->enrol_user($reporter->id, $course->id, 'student');

        $postid = $this->create_post($author->id, $course->id);
        $this->setUser($reporter);

        $result = report_item::execute('post', $postid, 'Contenido inapropiado');

        $this->assertTrue($result['status']);

        $report = $DB->get_record('local_chuspasocial_report', [
            'reporterid' => $reporter->id,
            'itemtype' => 'post',
            'itemid' => $postid,
        ]);

        $this->assertNotFalse($report);
        $this->assertSame('Contenido inapropiado', $report->reason);
        $this->assertSame('open', $report->status);
    }

    /**
     * A user without the report capability cannot report content.
     */
    public function test_execute_without_permission(): void {
        $this->resetAfterTest();

        $this->set_report_permission(CAP_PROHIBIT);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $author = $generator->create_user();
        $reporter = $generator->create_user();

        $generator->enrol_user($author->id, $course->id, 'student');
        $generator->enrol_user($reporter->id, $course->id, 'student');

        $postid = $this->create_post($author->id, $course->id);
        $this->setUser($reporter);

        $this->expectException(\required_capability_exception::class);

        report_item::execute('post', $postid, 'Contenido inapropiado');
    }

    /**
     * The returned data matches the declared external structure.
     */
    public function test_execute_returnvalue_is_clean(): void {
        $this->resetAfterTest();

        $this->set_report_permission(CAP_ALLOW);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $author = $generator->create_user();
        $reporter = $generator->create_user();

        $generator->enrol_user($author->id, $course->id, 'student');
        $generator->enrol_user($reporter->id, $course->id, 'student');

        $postid = $this->create_post($author->id, $course->id);
        $this->setUser($reporter);

        $result = report_item::execute('post', $postid, 'Reporte de prueba');

        $clean = external_api::clean_returnvalue(
            report_item::execute_returns(),
            $result
        );

        $this->assertSame($result, $clean);
    }
}
