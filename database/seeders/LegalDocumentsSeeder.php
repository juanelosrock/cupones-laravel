<?php
namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            [
                'type' => 'terms',
                'title' => 'Términos y Condiciones de Uso - CuponesHub',
                'version' => '1.0',
                'content' => <<<HTML
<h2>1. ACEPTACIÓN DE LOS TÉRMINOS</h2>
<p>Al acceder y usar la plataforma CuponesHub, usted acepta estar sujeto a estos Términos y Condiciones. Si no está de acuerdo con alguna parte de estos términos, no podrá acceder al servicio.</p>

<h2>2. DESCRIPCIÓN DEL SERVICIO</h2>
<p>CuponesHub es una plataforma digital que permite la generación, distribución y redención de cupones de descuento para productos y servicios. Los cupones pueden ser de tipo porcentaje o valor fijo y están sujetos a condiciones específicas de uso.</p>

<h2>3. USO DE LOS CUPONES</h2>
<ul>
<li>Cada cupón tiene una fecha de inicio y vencimiento. No será válido fuera de ese período.</li>
<li>Los cupones tienen un valor mínimo y máximo de compra requerido para su redención.</li>
<li>Los cupones de un solo uso no podrán redimirse más de una vez.</li>
<li>Los cupones no son transferibles ni canjeables por dinero en efectivo.</li>
<li>La plataforma se reserva el derecho de cancelar cupones en caso de uso fraudulento.</li>
</ul>

<h2>4. RESTRICCIONES</h2>
<p>Queda prohibido el uso de medios automatizados para obtener, generar o redimir cupones de manera masiva sin autorización expresa.</p>

<h2>5. MODIFICACIONES</h2>
<p>CuponesHub se reserva el derecho de modificar estos términos en cualquier momento. Los cambios serán notificados a través de los canales disponibles.</p>

<h2>6. LEY APLICABLE</h2>
<p>Estos términos se rigen por las leyes de la República de Colombia.</p>
HTML,
            ],
            [
                'type' => 'privacy',
                'title' => 'Política de Privacidad y Tratamiento de Datos Personales',
                'version' => '1.0',
                'content' => <<<HTML
<h2>1. RESPONSABLE DEL TRATAMIENTO</h2>
<p>CuponesHub es el responsable del tratamiento de los datos personales recolectados a través de esta plataforma, en cumplimiento de la Ley 1581 de 2012 y el Decreto 1377 de 2013 de Colombia.</p>

<h2>2. DATOS RECOLECTADOS</h2>
<p>Recolectamos la siguiente información personal:</p>
<ul>
<li>Nombre y apellido</li>
<li>Número de documento de identidad</li>
<li>Número de teléfono celular</li>
<li>Correo electrónico</li>
<li>Ciudad de residencia</li>
<li>Fecha de nacimiento</li>
<li>Historial de redención de cupones</li>
</ul>

<h2>3. FINALIDAD DEL TRATAMIENTO</h2>
<p>Los datos recolectados serán utilizados para:</p>
<ul>
<li>Gestión y validación de cupones de descuento</li>
<li>Envío de comunicaciones comerciales y promocionales vía SMS y/o correo electrónico</li>
<li>Análisis estadísticos de uso de la plataforma</li>
<li>Cumplimiento de obligaciones legales</li>
<li>Mejora de servicios y experiencia del usuario</li>
</ul>

<h2>4. DERECHOS DEL TITULAR</h2>
<p>Como titular de datos personales, usted tiene derecho a conocer, actualizar, rectificar y suprimir su información. Para ejercer estos derechos, puede contactarnos a través de los canales oficiales de la plataforma.</p>

<h2>5. TRANSFERENCIA DE DATOS</h2>
<p>Sus datos no serán vendidos a terceros. Podrán ser compartidos únicamente con aliados comerciales bajo estrictos acuerdos de confidencialidad y con las mismas finalidades descritas.</p>

<h2>6. SEGURIDAD</h2>
<p>Implementamos medidas técnicas y administrativas para proteger su información personal contra acceso no autorizado, divulgación, alteración o destrucción.</p>

<h2>7. VIGENCIA</h2>
<p>Sus datos serán conservados mientras mantenga una relación activa con la plataforma o hasta que solicite su eliminación, salvo obligación legal de conservarlos.</p>
HTML,
            ],
            [
                'type' => 'sms_consent',
                'title' => 'Autorización para Envío de Mensajes SMS Comerciales',
                'version' => '1.0',
                'content' => <<<HTML
<h2>AUTORIZACIÓN ENVÍO DE SMS</h2>
<p>Al aceptar este documento, usted autoriza expresamente a CuponesHub para:</p>
<ul>
<li>Enviar mensajes de texto (SMS) a su número de celular registrado</li>
<li>Comunicarle ofertas, descuentos, cupones y promociones comerciales</li>
<li>Notificarle sobre la activación, vencimiento o uso de sus cupones</li>
</ul>

<h2>FRECUENCIA</h2>
<p>La frecuencia de envío puede variar según las campañas activas. No se garantiza un número fijo de mensajes al mes.</p>

<h2>CANCELACIÓN</h2>
<p>Puede cancelar la suscripción a los SMS en cualquier momento respondiendo STOP al número remitente o solicitándolo directamente en la plataforma. La cancelación no afecta la validez de sus cupones existentes.</p>

<h2>COSTOS</h2>
<p>El envío de SMS es gratuito para el receptor. Pueden aplicar cargos estándar de su operador según su plan de datos o minutos.</p>
HTML,
            ],
        ];

        foreach ($docs as $doc) {
            LegalDocument::firstOrCreate(
                ['type' => $doc['type'], 'version' => $doc['version']],
                array_merge($doc, ['is_active' => true, 'published_at' => now()])
            );
        }

        $this->command->info('Documentos legales iniciales creados.');
    }
}