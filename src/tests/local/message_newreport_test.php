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

namespace local_chuspasocial\local;
/**
 * Pruebas para la notificación newreport.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\report_service::create_report
 */
class message_newreport_test extends \advanced_testcase {
    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_newreport_notifica_al_moderador_y_no_al_reportante(): void {
        global $DB;

        // Registrar la capacidad si Moodle todavía no la reconoce.
        $capability = 'local/chuspasocial:moderate';

        if (!get_capability_info($capability)) {
            $DB->insert_record('capabilities', (object) [
                'name' => $capability,
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'component' => 'local_chuspasocial',
                'riskbitmask' => 0,
            ]);

            \cache::make('core', 'capabilities')->purge();
        }

        $generator = $this->getDataGenerator();

        // Crear el usuario que genera el reporte y el moderador.
        $reporter = $generator->create_user();
        $moderator = $generator->create_user();

        // Crear un rol con permiso para moderar.
        $roleid = create_role(
            'Moderador de pruebas',
            'testmoderator',
            'Rol utilizado para probar las notificaciones de reportes'
        );

        assign_capability(
            $capability,
            CAP_ALLOW,
            $roleid,
            \context_system::instance()->id
        );

        role_assign(
            $roleid,
            $moderator->id,
            \context_system::instance()->id
        );

        // Capturar los mensajes generados durante la creación del reporte.
        $sink = $this->redirectMessages();

        $this->setUser($reporter);

        report_service::create_report(
            'post',
            101,
            'Contenido inapropiado'
        );

        $messages = $sink->get_messages();
        $sink->close();

        // Debe enviarse una notificación al moderador.
        $this->assertCount(1, $messages);
        $this->assertEquals($moderator->id, $messages[0]->useridto);

        // Verificar que el asunto identifique el contenido reportado.
        $this->assertStringContainsString(
            'post #101',
            $messages[0]->subject
        );

        // Verificar que el cuerpo identifique el contenido reportado.
        $this->assertStringContainsString(
            'post #101',
            $messages[0]->fullmessage
        );

        // Verificar que el cuerpo incluya el motivo del reporte.
        $this->assertStringContainsString(
            'Contenido inapropiado',
            $messages[0]->fullmessage
        );

        // El reportante no debe recibir la notificación.
        foreach ($messages as $message) {
            $this->assertNotEquals($reporter->id, $message->useridto);
        }
    }
}
