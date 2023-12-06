<?php

namespace Model;

use Model\ActiveRecord;

class Usuario extends ActiveRecord
{
  protected static $tabla = 'usuarios';
  protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

  public function __construct($args = [])
  {
    $this->id = $args['id'] ?? null;
    $this->nombre = $args['nombre'] ?? '';
    $this->email = $args['email'] ?? '';
    $this->password = $args['password'] ?? '';
    $this->password2 = $args['password2'] ?? '';
    $this->password_actual = $args['password_actual'] ?? '';
    $this->password_nuevo = $args['password_nuevo'] ?? '';
    $this->token = $args['token'] ?? '';
    $this->confirmado = $args['confirmado'] ?? 0;
  }

  // Validacion para cuentas nuevas
  public function validarNuevaCuenta()
  {
    if (!$this->nombre) {
      self::$alertas['error'][] = "El nombre del usuario es obligatorio";
    }

    if (!$this->email) {
      self::$alertas['error'][] = "El email es obligatorio";
    }

    if (!$this->password) {
      self::$alertas['error'][] = "La contraseña es obligatoria";
    }

    if (strlen($this->password) < 6) {
      self::$alertas['error'][] = "La contraseña debe tener al menos 6 caracteres";
    }

    if ($this->password !== $this->password2) {
      self::$alertas['error'][] = "Las contraseñas no son iguales";
    }

    return self::$alertas;
  }

  // Valida un email
  public function validarEmail()
  {
    if (!$this->email) {
      self::$alertas['error'][] = "El email es obligatorio";
    }

    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      self::$alertas['error'][] = "El email no es valido";
    }

    return self::$alertas;
  }

  // Validar la contraseña
  public function validarPassword()
  {
    if (!$this->password) {
      self::$alertas['error'][] = "La contraseña es obligatoria";
    }

    if (strlen($this->password) < 6) {
      self::$alertas['error'][] = "La contraseña debe tener al menos 6 caracteres";
    }

    return self::$alertas;
  }

  // Validar el login
  public function validarLogin()
  {
    if (!$this->email) {
      self::$alertas['error'][] = "El email es obligatorio";
    }

    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      self::$alertas['error'][] = "El email no es valido";
    }

    if (!$this->password) {
      self::$alertas['error'][] = "La contraseña es obligatoria";
    }

    return self::$alertas;
  }

  // Valida el perfil
  public function validarPerfil()
  {
    if (!$this->nombre) {
      self::$alertas['error'][] = "El nombre del usuario es obligatorio";
    }

    if (!$this->email) {
      self::$alertas['error'][] = "El email es obligatorio";
    }

    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
      self::$alertas['error'][] = "El email no es valido";
    }

    return self::$alertas;
  }

  public function validarNuevoPassword()
  {
    if (!$this->password_actual) {
      self::$alertas['error'][] = "La contraseña actual es obligatoria";
    }

    if (!$this->password_nuevo) {
      self::$alertas['error'][] = "La contraseña nueva es obligatoria";
    }

    if (strlen($this->password_nuevo) < 6) {
      self::$alertas['error'][] = "La contraseña debe tener al menos 6 caracteres";
    }

    return self::$alertas;
  }

  // Comprueba si el password es correcto
  public function comprobarPassword()
  {
    if (!password_verify($this->password_actual, $this->password)) {
      self::$alertas['error'][] = "La contraseña actual es incorrecta";
      return false;
    }

    return true;
  }

  // Hashea la contraseña
  public function hashPassword()
  {
    $this->password = password_hash($this->password, PASSWORD_BCRYPT);
  }

  // Genera un token
  public function crearToken()
  {
    $this->token = uniqid();
  }
}
