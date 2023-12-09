
## ¡Bienvenido al Juego Multijugador en Laravel! Sumérgete en esta emocionante experiencia colaborativa donde podrás jugar y competir con otros usuarios. Echa un vistazo a las rutas que te guiarán a través de esta emocionante aventura

1. **Entrar al Juego:**
   - Envía una solicitud POST a `http://localhost:8000/api/unirse` con el siguiente formato:
     ```json
     {"nombre": "jugador1"}
     ```

2. **Generar Palabra:**
   - Genera una palabra aleatoria utilizando la ruta `http://localhost:8000/api/palabra`.

3. **Listar Usuarios:**
   - Obtiene la lista de usuarios en el juego haciendo una solicitud GET a `http://localhost:8000/api/jugadores`.

4. **Iniciar Juego:**
   - Envía una solicitud POST a `http://localhost:8000/api/comenzar` con el siguiente formato:
     ```json
     {"nombre": "jugador1", "letra": "e"}
     ```
5. **Reiniciar Juego:**
   - Utiliza la ruta `http://localhost:8000/api/reset` para reiniciar el juego si es necesario.

Considerar que se ingresan los jugadores, luego generamos la palabra y luego si con el nombre y la letra enviamos en iniciar, asi funciona adecuadamente

Dejo las Roote aqui, todo es el puerto 8000: 

Route::post('comenzar', [JuegoController::class, 'iniciarJuego']);
Route::post('unirse', [JuegoController::class, 'ingresarJuego']);
Route::get('jugadores', [JuegoController::class, 'listarUsuarios']);
Route::get('palabra', [JuegoController::class, 'generarPalabraAleatoria']);
Route::post('reset', [JuegoController::class, 'reiniciarJuego']);
