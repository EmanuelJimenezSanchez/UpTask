<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController
{
  public static function login(Router $router)
  {
    $alertas = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $auth = new Usuario($_POST);

      $alertas = $auth->validarLogin();

      if (empty($alertas)) {
        // Verificar si el usuario existe
        $usuario = Usuario::where('email', $auth->email);

        if ($usuario && $usuario->confirmado) {
          // Verificar si el password es correcto
          if (password_verify($auth->password, $usuario->password)) {
            // Init la sesion
            session_start();
            $_SESSION['id'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['login'] = TRUE;

            // Redireccionar al usuario
            header('Location: /dashboard');
          } else {
            // Mostrar error
            Usuario::setAlerta('error', 'La contraseña es incorrecta');
          }
        } else {
          // Mostrar error
          Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
        }
      }
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('auth/login', [
      'titulo' => 'Iniciar Sesión',
      'alertas' => $alertas
    ]);
  }

  public static function logout()
  {
    session_start();
    $_SESSION = [];
    header('Location: /');
  }

  public static function crear(Router $router)
  {
    $usuario = new Usuario;
    $alertas = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $usuario->sincronizar($_POST);

      $alertas = $usuario->validarNuevaCuenta();

      // Validar que todos los campos fueron llenados correctamente
      if (empty($alertas)) {
        // Verificar si el usuario ya existe
        $existeUsuario = Usuario::where('email', $usuario->email);

        if (!$existeUsuario) {
          // Hashear la contraseña
          $usuario->hashPassword();

          // Eliminar la confirmacion de la contraseña
          unset($usuario->password2);

          // Generar un token
          $usuario->crearToken();

          // Crear el usuario
          $resultado = $usuario->guardar();

          if ($resultado) {
            // Enviar el email
            $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
            $email->enviarConfirmacion();

            // Redireccionar al usuario
            header('Location: /mensaje');
          }
        } else {
          // Mostrar error
          Usuario::setAlerta('error', 'El usuario ya existe');
        }
      }
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('auth/crear', [
      'titulo' => 'Crear Cuenta',
      'usuario' => $usuario,
      'alertas' => $alertas
    ]);
  }

  public static function olvide(Router $router)
  {
    $alertas = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $usuario = new Usuario($_POST);
      $alertas = $usuario->validarEmail();

      if (empty($alertas)) {
        // Verificar si el usuario existe
        $usuario = Usuario::where('email', $usuario->email);

        if ($usuario && $usuario->confirmado) {
          // Generar un token
          $usuario->crearToken();

          // Guardar el token en la base de datos
          $usuario->guardar();

          // Enviar el email
          $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
          $email->enviarReestablecer();

          // Imprimir la alerta
          Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
        } else {
          Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
        }
      }
    }
    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('auth/olvide', [
      'titulo' => 'Olvide Contraseña',
      'alertas' => $alertas
    ]);
  }

  public static function reestablecer(Router $router)
  {
    $token = s($_GET['token']);
    $mostrar = TRUE;

    if (!$token) header('Location: /');

    // Encontrar al usuario con el token
    $usuario = Usuario::where('token', $token);

    if (empty($usuario)) {
      Usuario::setAlerta('error', 'Token no válido');
      $mostrar = FALSE;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Anadir la nueva contraseña
      $usuario->sincronizar($_POST);

      // Validar el password
      $alertas = $usuario->validarPassword();

      if (empty($alertas)) {
        // Hashear el password
        $usuario->hashPassword();

        // Eliminar el token
        $usuario->token = null;

        // Guardar en la base de datos
        $usuario->guardar();

        // Mostrar la alerta
        Usuario::setAlerta('exito', 'Contraseña actualizada correctamente');
        $mostrar = FALSE;
      }
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('auth/reestablecer', [
      'titulo' => 'Reestablecer Contraseña',
      'alertas' => $alertas,
      'mostrar' => $mostrar
    ]);
  }

  public static function mensaje(Router $router)
  {
    // Renderizar la vista
    $router->render('auth/mensaje', [
      'titulo' => 'Cuenta Creada Exitosamente'
    ]);
  }

  public static function confirmar(Router $router)
  {
    $token = s($_GET['token']);

    if (!$token) header('Location: /');

    // Encontrar al usuario con el token
    $usuario = Usuario::where('token', $token);

    if (empty($usuario)) {
      Usuario::setAlerta('error', 'Token no válido');
    } else {
      // Confirmar la cuenta
      $usuario->confirmado = 1;
      $usuario->token = null;
      unset($usuario->password2);

      // Guardar en la base de datos
      $usuario->guardar();
      Usuario::setAlerta('exito', 'Cuenta comprobada exitosamente');
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('auth/confirmar', [
      'titulo' => 'Confirmar Cuenta',
      'alertas' => $alertas
    ]);
  }
}
