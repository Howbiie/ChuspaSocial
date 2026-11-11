<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_chuspasocial\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Tests for the ChuspaSocial privacy provider.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends provider_testcase {

    /**
     * Creates a test post.
     *
     * @param int $userid User ID.
     * @param int $courseid Course ID.
     * @return int Post ID.
     */
    private function create_post(int $userid, int $courseid): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Publicacion de prueba',
            'contentformat' => FORMAT_PLAIN,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Tests that user data is exported.
     */
    public function test_export_user_data(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        $postid = $this->create_post($user->id, $course->id);
        $context = \context_course::instance($course->id);

        $contextlist = new approved_contextlist(
            $user,
            'local_chuspasocial',
            [$context->id]
        );

        provider::export_user_data($contextlist);

        $writer = writer::with_context($context);
        $subcontext = [
            get_string('pluginname', 'local_chuspasocial'),
            'posts',
            (string) $postid,
        ];

        $exported = $writer->get_data($subcontext);

        $this->assertNotEmpty($exported);
        $this->assertEquals('Publicacion de prueba', $exported->content);
    }

    /**
     * Tests that user data is deleted.
     */
    public function test_delete_data_for_user(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();

        $postid = $this->create_post($user->id, $course->id);
        $context = \context_course::instance($course->id);

        $contextlist = new approved_contextlist(
            $user,
            'local_chuspasocial',
            [$context->id]
        );

        $this->assertTrue($DB->record_exists('local_chuspasocial_post', [
            'id' => $postid,
        ]));

        provider::delete_data_for_user($contextlist);

        $this->assertFalse($DB->record_exists('local_chuspasocial_post', [
            'id' => $postid,
        ]));
    }
}
