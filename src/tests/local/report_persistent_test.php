
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
 * Unit tests for the report persistent.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local\persistent;

use advanced_testcase;

/**
 * Pruebas unitarias para la entidad report.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\persistent\report
 */
class report_persistent_test extends advanced_testcase {

    /**
     * Configuración previa a cada prueba.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Crea un reporte de prueba.
     *
     * @param int $reporterid ID del usuario que reporta.
     * @return report
     */
    private function create_test_report(int $reporterid): report {
        $record = new \stdClass();
        $record->itemtype = 'post';
        $record->itemid = 1;
        $record->reporterid = $reporterid;
        $record->reason = 'Reporte de prueba';

        $report = new report(0, $record);
        $report->create();

        return $report;
    }

    /**
     * Comprueba la transición de pendiente a revisado.
     */
    public function test_update_status_to_reviewed(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $reporter = $generator->create_user();
        $moderator = $generator->create_user();

        $report = $this->create_test_report($reporter->id);

        $this->assertEquals(report::STATUS_PENDING, $report->get('status'));

        $oldtime = time() - 60;
        $DB->set_field(
            report::TABLE,
            'timemodified',
            $oldtime,
            ['id' => $report->get('id')]
        );

        $report = new report($report->get('id'));
        $report->set('status', report::STATUS_REVIEWED);
        $report->set('resolvedby', $moderator->id);
        $report->update();

        $updated = new report($report->get('id'));

        $this->assertEquals(report::STATUS_REVIEWED, $updated->get('status'));
        $this->assertEquals($moderator->id, $updated->get('resolvedby'));
        $this->assertGreaterThan($oldtime, $updated->get('timemodified'));
    }

    /**
     * Comprueba la transición de pendiente a descartado.
     */
    public function test_update_status_to_dismissed(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $reporter = $generator->create_user();
        $moderator = $generator->create_user();

        $report = $this->create_test_report($reporter->id);

        $this->assertEquals(report::STATUS_PENDING, $report->get('status'));

        $oldtime = time() - 60;
        $DB->set_field(
            report::TABLE,
            'timemodified',
            $oldtime,
            ['id' => $report->get('id')]
        );

        $report = new report($report->get('id'));
        $report->set('status', report::STATUS_DISMISSED);
        $report->set('resolvedby', $moderator->id);
        $report->update();

        $updated = new report($report->get('id'));

        $this->assertEquals(report::STATUS_DISMISSED, $updated->get('status'));
        $this->assertEquals($moderator->id, $updated->get('resolvedby'));
        $this->assertGreaterThan($oldtime, $updated->get('timemodified'));
    }
}
