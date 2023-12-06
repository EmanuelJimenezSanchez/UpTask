<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController
{
  public static function index()
  {
    session_start();

    $proyectoId = $_GET['id'];

    if (!$proyectoId) header('Location: /dashboard');

    $proyecto = Proyecto::where('url', $proyectoId);

    if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) header('Location: /404');

    $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);

    echo json_encode(['tareas' => $tareas]);
  }

  public static function crear()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      session_start();

      $proyectoId = $_POST['proyectoId'];
      $proyecto = Proyecto::where('url', $proyectoId);

      if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al agregar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      // Todo bien, instanciar y crear la tarea
      $tarea = new Tarea($_POST);
      $tarea->proyectoId = $proyecto->id;

      $resultado = $tarea->guardar();

      if (!$resultado) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al agregar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      $respuesta = [
        'tipo' => 'exito',
        'id' => $resultado['id'],
        'mensaje' => 'Tarea agregada correctamente',
        'proyectoId' => $proyecto->id
      ];

      echo json_encode($respuesta);
    }
  }

  public static function actualizar()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      session_start();

      // Busca el proyecto en la base de datos
      $proyecto = Proyecto::where('url', $_POST['proyectoId']);

      // Valida que el proyecto exista y que el usuario sea el propietario
      if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al actualizar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      // Instanciar la tarea con el nuevo estado
      $tarea = new Tarea($_POST);
      $tarea->proyectoId = $proyecto->id;

      $resultado = $tarea->guardar();

      if (!$resultado) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al actualizar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      $respuesta = [
        'tipo' => 'exito',
        'id' => $tarea->id,
        'mensaje' => 'Tarea actualizada correctamente',
        'estado' => $tarea->estado,
        'proyectoId' => $proyecto->id
      ];

      echo json_encode($respuesta);
    }
  }

  public static function eliminar()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      session_start();

      // Busca el proyecto en la base de datos
      $proyecto = Proyecto::where('url', $_POST['proyectoId']);

      // Valida que el proyecto exista y que el usuario sea el propietario
      if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al eliminar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      // Eliminar la tarea
      $tarea = new Tarea($_POST);
      $tarea->proyectoId = $proyecto->id;

      $resultado = $tarea->eliminar();

      if (!$resultado) {
        $respuesta = [
          'tipo' => 'error',
          'mensaje' => 'Hubo un error al eliminar la tarea'
        ];
        echo json_encode($respuesta);
        return;
      }

      $respuesta = [
        'tipo' => 'exito',
        'mensaje' => 'Tarea eliminada correctamente'
      ];

      echo json_encode($respuesta);
    }
  }
}
