<?php

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController
{
  public static function index(Router $router)
  {
    session_start();
    isAuth();

    $id = $_SESSION['id'];

    $proyectos = Proyecto::belongsTo('propietarioId', $id);

    // Renderizar la vista
    $router->render('dashboard/index', [
      'titulo' => 'Proyectos',
      'proyectos' => $proyectos
    ]);
  }

  public static function crear_proyecto(Router $router)
  {
    session_start();
    isAuth();

    $alertas = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $proyecto = new Proyecto($_POST);

      // Validacion
      $alertas = $proyecto->validarProyecto();

      if (empty($alertas)) {
        // Generar una URL unica
        $proyecto->url = md5(uniqid());

        // Almacenar el creador del proyecto
        $proyecto->propietarioId = $_SESSION['id'];

        // Crear el proyecto
        $resultado = $proyecto->guardar();

        if ($resultado) {
          header('Location: /proyecto?id=' . $proyecto->url);
        }
      }
    }

    $alertas = Proyecto::getAlertas();

    // Renderizar la vista
    $router->render('dashboard/crear-proyecto', [
      'titulo' => 'Crear Proyecto',
      'alertas' => $alertas
    ]);
  }

  public static function proyecto(Router $router)
  {
    session_start();
    isAuth();

    $url = $_GET['id'];
    $proyecto = Proyecto::where('url', $url);
    $alertas = [];

    // Revisar que la persona que visita el proyecto sea el propietario
    if ($_SESSION['id'] !== $proyecto->propietarioId) {
      header('Location: /dashboard');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      debuguear('submit');
    }

    // Renderizar la vista
    $router->render('dashboard/proyecto', [
      'titulo' => $proyecto->proyecto,
      'alertas' => $alertas
    ]);
  }

  public static function perfil(Router $router)
  {
    session_start();
    isAuth();

    $alertas = [];
    $usuario = Usuario::find($_SESSION['id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Asignar los valores
      $usuario->sincronizar($_POST);

      // Validar
      $alertas = $usuario->validarPerfil();

      if (empty($alertas)) {
        // Buscar si ya existe el email con otro usuario
        $existeEmail = Usuario::where('email', $usuario->email);

        if ($existeEmail && $existeEmail->id !== $usuario->id) {
          $alertas = Usuario::setAlerta('error', 'El email ya esta registrado');
        } else {
          // Guardar el usuario
          $resultado = $usuario->guardar();

          if (!$resultado) {
            // alerta de error
            $alertas = Usuario::setAlerta('error', 'No se pudo actualizar el perfil');
          }

          $_SESSION['nombre'] = $usuario->nombre;
          $_SESSION['email'] = $usuario->email;

          Usuario::setAlerta('exito', 'Perfil actualizado correctamente');
        }
      }
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('dashboard/perfil', [
      'titulo' => 'Perfil',
      'usuario' => $usuario,
      'alertas' => $alertas
    ]);
  }

  public static function cambiar_password(Router $router)
  {
    session_start();
    isAuth();

    $alertas = [];
    $usuario = Usuario::find($_SESSION['id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Asignar los valores
      $usuario->sincronizar($_POST);

      // Validar
      $alertas = $usuario->validarNuevoPassword();

      if (empty($alertas)) {
        // Verificar que el password actual sea correcto
        $resultado = $usuario->comprobarPassword();

        if ($resultado) {
          // Asignar el nuevo password
          $usuario->password = $usuario->password_nuevo;

          // Hashear el nuevo password
          $usuario->hashPassword();

          // Eliminar los campos que no queremos actualizar
          unset($usuario->password_actual);
          unset($usuario->password_nuevo);
          unset($usuario->password2);

          // Guardar el usuario
          $resultado = $usuario->guardar();

          if (!$resultado) {
            // alerta de error
            $alertas = Usuario::setAlerta('error', 'No se pudo actualizar el password');
          }

          Usuario::setAlerta('exito', 'Password actualizado correctamente');
        }
      }
    }

    $alertas = Usuario::getAlertas();

    // Renderizar la vista
    $router->render('dashboard/cambiar-password', [
      'titulo' => 'Cambiar Password',
      'usuario' => $usuario,
      'alertas' => $alertas
    ]);
  }
}
