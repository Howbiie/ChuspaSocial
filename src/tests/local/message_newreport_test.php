<?php
namespace local_chuspasocial\local;

/**
 * Pruebas para la notificación newreport.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\report_service::create_report
 */
class message_newreport_test extends \advanced_testcase {

    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_newreport_notifica_al_moderador_y_no_al_reportante(): void {
        global $DB;

        // Comprobar que la capacidad requerida existe.
        $capability = 'local/chuspasocial:moderate';
        $this->assertNotFalse(
            get_capability_info($capability),
            'La capacidad de moderación debe estar definida para probar la notificación.'
        );

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

        $data = (object) [
            'userid' => $reporter->id,
            'itemtype' => 'post',
            'itemid' => 101,
            'reason' => 'Contenido inapropiado',
        ];

        report_service::create_report($data);

        $messages = $sink->get_messages();
        $sink->close();

        // Debe enviarse una notificación al moderador.
        $this->assertCount(1, $messages);
        $this->assertEquals($moderator->id, $messages[0]->useridto);

        // El reportante no debe recibir la notificación.
        foreach ($messages as $message) {
            $this->assertNotEquals($reporter->id, $message->useridto);
        }
    }
}
