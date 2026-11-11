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

namespace local_chuspasocial;

/**
 * Tests for the ChuspaSocial plugin.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \core_plugin_manager
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {

    /**
     * Verifies that the plugin is installed and up to date.
     */
    public function test_plugin_is_installed(): void {
        $pluginmanager = \core_plugin_manager::instance();
        $plugininfo = $pluginmanager->get_plugin_info('local_chuspasocial');

        $this->assertNotNull($plugininfo);
        $this->assertTrue($plugininfo->is_installed_and_upgraded());
    }
}
