<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JuegoController extends Controller
{
    private $rutaDatos;

    public function __construct()
    {
        $this->rutaDatos = storage_path('app/data/');
    }

    private function obtenerArchivoDatos($nombreArchivo)
    {
        return $this->rutaDatos . $nombreArchivo . '.json';
    }

    private function leerDatos($nombreArchivo, $valorPredeterminado = [])
    {
        $archivo = $this->obtenerArchivoDatos($nombreArchivo);

        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            return json_decode($contenido, true);
        }

        return $valorPredeterminado;
    }

    private function escribirDatos($nombreArchivo, $datos)
    {
        $archivo = $this->obtenerArchivoDatos($nombreArchivo);
        $contenido = json_encode($datos, JSON_PRETTY_PRINT);
        file_put_contents($archivo, $contenido);
    }

    public function listarUsuarios()
    {
        $usuariosEnEspera = $this->leerDatos('usuarios_en_espera', []);
        return response()->json(['Usuarios' => $usuariosEnEspera], 200);
    }

    public function ingresarJuego(Request $solicitud)
    {
        $nombre = $solicitud->input('nombre');
        $usuariosEnEspera = $this->leerDatos('usuarios_en_espera', []);
        $conteoTurnos = $this->leerDatos('conteo_turnos', 1);

        if (count($usuariosEnEspera) < 3) {
            $turno = $conteoTurnos;
            $usuariosEnEspera[] = ['nombre' => $nombre, 'turno' => $turno];
            $conteoTurnos++;
            $this->escribirDatos('usuarios_en_espera', $usuariosEnEspera);
            $this->escribirDatos('conteo_turnos', $conteoTurnos);
            $usuario = ['nombre' => $nombre, 'turno' => $turno];
            return response()->json(['mensaje' => 'Usuario añadido a la lista de espera', 'Datos'=> $usuario], 200);
        } else {
            return response()->json(['mensaje' => 'La lista de espera está llena'], 200);
        }
    }

    public function generarPalabraAleatoria()
    {
        $palabras = ['gato', 'perro', 'sol', 'luna', 'casa', 'auto', 'mesa', 'flor', 'mar', 'pato'];
        $palabraAleatoria = $palabras[array_rand($palabras)];
        $this->escribirDatos('palabra_aleatoria', $palabraAleatoria);
        return response()->json(['mensaje' => 'Palabra aleatoria generada', 'Palabra'=> $palabraAleatoria], 200);
    }

    public function limpiarCache()
    {
        $this->escribirDatos('usuarios_en_espera', []);
        $this->escribirDatos('conteo_turnos', 1);

        return response()->json(['mensaje' => 'Datos eliminados'], 200);
    }

    public function iniciarJuego(Request $solicitud)
    {
        $usuariosEnEspera = $this->leerDatos('usuarios_en_espera', []);
        $palabra = $this->leerDatos('palabra_aleatoria', '');

        if (count($usuariosEnEspera) == 3 || $palabra != '') {
            $turnoActual = $this->leerDatos('turno_actual', 1);

            if ($turnoActual > count($usuariosEnEspera)) {
                $turnoActual = 1;
            }

            $nombre = $solicitud->input('nombre');

            if ($this->esTurnoJugador($usuariosEnEspera, $turnoActual, $nombre)) {
                $progresoJugador = $this->leerDatos("progreso_jugador_$nombre", []);

                $esCorrecta = $this->verificarSiLetraEsCorrecta($palabra, $solicitud->input('letra'));
                $progresoJugador = $this->actualizarProgresoJugador($progresoJugador, $solicitud->input('letra'), $esCorrecta, $nombre);
                $turnoActual++;
                $this->escribirDatos('turno_actual', $turnoActual);

                if ($esCorrecta) {
                    $mensaje = "Letra jugador $nombre, turno " . ($turnoActual - 1) . " es " . strtoupper($solicitud->input('letra'));
                    $mensaje .= ", Acertó";
                    $mensaje .= ", Progreso " . implode('', $progresoJugador);

                    if (!in_array('*', $progresoJugador)) {
                        $this->reiniciarJuego(); 
                        return response()->json(['mensaje' => $mensaje, 'estado_juego' => 'Juego terminado, palabra adivinada'], 200);
                    }

                    return response()->json(['mensaje' => $mensaje], 200);
                } else {
                    return response()->json(['mensaje' => "Letra jugador $nombre, turno " . ($turnoActual - 1) . " es " . strtoupper($solicitud->input('letra')) . ", Falló"], 200);
                }
            } else {
                return response()->json(['error' => 'No es tu turno o el nombre es incorrecto. Letra rechazada o alguien adivinó la palabra.'], 400);
            }
        } else {
            return response()->json(['mensaje' => 'No se puede iniciar el juego. Jugadores insuficientes.'], 200);
        }
    }

    public function reiniciarJuego()
    {
        $this->escribirDatos('usuarios_en_espera', []);
        $this->escribirDatos('conteo_turnos', 1);
        $this->escribirDatos('turno_actual', 1);
        $this->escribirDatos('palabra_aleatoria', '');

        return response()->json(['mensaje' => 'Juego reiniciado.'], 200);
    }

    private function actualizarProgresoJugador($progresoJugador, $letra, $esCorrecta, $nombreJugador)
    {
        if ($esCorrecta) {
            $palabra = $this->leerDatos('palabra_aleatoria', '');
            $progresoActualizado = [];

            foreach (str_split($palabra) as $indice => $caracter) {
                if (strtoupper($caracter) === strtoupper($letra) || (isset($progresoJugador[$indice]) && $progresoJugador[$indice] !== '*')) {
                    $progresoActualizado[] = strtoupper($caracter);
                } else {
                    $progresoActualizado[] = '*';
                }
            }

            foreach ($progresoActualizado as $indice => $caracter) {
                $progresoJugador[$indice] = $caracter;
            }

            $this->escribirDatos("progreso_jugador_$nombreJugador", $progresoJugador);
        }

        return $progresoJugador;
    }

    private function esTurnoJugador($usuariosEnEspera, $turnoActual, $nombre)
    {
        foreach ($usuariosEnEspera as $usuario) {
            if ($usuario['nombre'] === $nombre && $usuario['turno'] === $turnoActual) {
                return true;
            }
        }

        return false;
    }

    private function verificarSiLetraEsCorrecta($palabra, $letra)
    {
        return stripos($palabra, $letra) !== false;
    }
}
