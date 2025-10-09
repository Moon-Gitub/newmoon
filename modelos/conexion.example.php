<?php

/**
 * ARCHIVO DE EJEMPLO - CONEXIÓN A BASE DE DATOS
 * 
 * INSTRUCCIONES:
 * 1. Copiar este archivo como "conexion.php"
 * 2. Reemplazar los valores de ejemplo con los reales
 * 3. NUNCA subir conexion.php a GitHub (está en .gitignore)
 */

class Conexion{

	// ⚠️ CAMBIAR ESTOS VALORES POR LOS REALES
	static public $hostDB = 'localhost';
	static public $nameDB = 'nombre_base_datos';
	static public $userDB = 'usuario_bd';
	static public $passDB = 'contraseña_segura_aqui';
	static public $charset = 'UTF8MB4';

	static public function getDatosConexion(){

		return array(
			'host' => self::$hostDB,
			'db' => self::$nameDB,
			'user' => self::$userDB,
			'pass' => self::$passDB,
			'charset' => self::$charset
		);
	}

	static public function conectar(){
		$host = self::$hostDB;
		$db = self::$nameDB;
		$user = self::$userDB;
		$pass = self::$passDB;

		try {
			$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
			$link->exec("set names utf8");
			return $link;
			
		} catch (PDOException $e) {
			error_log("Error de conexión: " . $e->getMessage());
			throw new Exception("Error de conexión a la base de datos");
		}
	}

	//CONECTAR A BD MOON PARA VER ESTADO CLIENTE
	static public function conectarMoon(){

		// ⚠️ CAMBIAR ESTOS VALORES POR LOS REALES
		$host = '107.161.23.241';
		$db = 'nombre_base_moon';
		$user = 'usuario_moon';
		$pass = 'contraseña_moon';

		try {
			$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
			$link->exec("set names utf8");
			return $link;
			
		} catch (PDOException $e) {
			error_log("Error de conexión Moon: " . $e->getMessage());
			throw new Exception("Error de conexión a sistema de cobro");
		}
	}

}

