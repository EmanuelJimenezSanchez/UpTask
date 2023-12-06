<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;
use Resend;

class Email
{
  protected $email;
  protected $nombre;
  protected $token;

  public function __construct($email, $nombre, $token)
  {
    $this->email = $email;
    $this->nombre = $nombre;
    $this->token = $token;
  }

  public function enviarConfirmacion()
  {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Port = 2525;
    $mail->Username = '4da4b78eaee94d';
    $mail->Password = '271cac2ec01b67';

    $mail->setFrom('cuentas@uptask.com');
    $mail->addAddress('cuentas@uptask.com', 'Uptask.com');
    $mail->Subject = 'Confirma tu cuenta';

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $contenido = "<html>";
    $contenido .= "<p><strong>Hola " . $this->nombre . "</strong>, has creado tu cuenta en UpTask, solo debes confirmarla en el siguiente enlace</p>";
    $contenido .= "<p>Presiona aqui: <a href='" . $_ENV['APP_URL'] . "/confirmar?token=" . $this->token . "'>Confirmar Cuenta</a></p>";
    $contenido .= "<p>Si no has sido tu, ignora este mensaje</p>";
    $contenido .= "</html>";

    $mail->Body = $contenido;

    // Enviar el email
    $mail->send();

    $resend = Resend::client($_ENV['EMAIL_KEY']);

    $resend->emails->send([
      'from' => 'Acme <onboarding@resend.dev>',
      'to' => ['delivered@resend.dev'],
      'subject' => 'hello world',
      'html' => '<strong>it works!</strong>',
    ]);
  }

  public function enviarReestablecer()
  {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = $_ENV['EMAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Port = $_ENV['EMAIL_PORT'];
    $mail->Username = $_ENV['EMAIL_USER'];
    $mail->Password = $_ENV['EMAIL_PASS'];

    $mail->setFrom('cuentas@uptask.com');
    $mail->addAddress('cuentas@uptask.com', 'Uptask.com');
    $mail->Subject = 'Confirma tu cuenta';

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $contenido = "<html>";
    $contenido .= "<p><strong>Hola " . $this->nombre . "</strong>, has solicitado reestablecer tu contraseña en UpTask, solo debes confirmarla en el siguiente enlace</p>";
    $contenido .= "<p>Presiona aqui: <a href='" . $_ENV['APP_URL'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Contraseña</a></p>";
    $contenido .= "<p>Si no has sido tu, ignora este mensaje</p>";
    $contenido .= "</html>";

    $mail->Body = $contenido;
    $mail->send();
  }
}
