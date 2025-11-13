<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @OA\Tag(
 *     name="ProveedorController",
 *     description="Operaciones sobre proveedores"
 * )
 */
class ProveedorController extends Controller
{
    /**
     * Crear un nuevo proveedor con artículos asociados.
     *
     * @OA\Post(
     *     path="/api/proveedores",
     *     tags={"ProveedorController"},
     *     summary="Crear un nuevo proveedor",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="identificador_externo", type="string", example="PROV-001"),
     *                 @OA\Property(property="nombre_comercial", type="string", example="Distribuidora ABC"),
     *                 @OA\Property(property="calendario_siempre_abierto", type="boolean", example=true),
     *                 @OA\Property(property="horario_lunes_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_lunes_fin", type="string", format="time", example="18:00:00"),
     *                 @OA\Property(property="horario_martes_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_martes_fin", type="string", format="time", example="18:00:00"),
     *                 @OA\Property(property="horario_miercoles_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_miercoles_fin", type="string", format="time", example="18:00:00"),
     *                 @OA\Property(property="horario_jueves_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_jueves_fin", type="string", format="time", example="18:00:00"),
     *                 @OA\Property(property="horario_viernes_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_viernes_fin", type="string", format="time", example="18:00:00"),
     *                 @OA\Property(property="horario_sabado_inicio", type="string", format="time", example="09:00:00"),
     *                 @OA\Property(property="horario_sabado_fin", type="string", format="time", example="14:00:00"),
     *                 @OA\Property(property="horario_domingo_inicio", type="string", format="time", example=null),
     *                 @OA\Property(property="horario_domingo_fin", type="string", format="time", example=null),
     *                 @OA\Property(property="articulos[]", type="array", @OA\Items(type="string"), example={"ART-001", "ART-002"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proveedor creado exitosamente."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de datos."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function store(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));

        try {
            // Validación de datos
            $validated = $request->validate([
                'identificador_externo' => 'required|string|unique:proveedores,identificador_externo',
                'nombre_comercial' => 'required|string|max:255',
                'calendario_siempre_abierto' => 'required|boolean',
                'horario_lunes_inicio' => 'nullable|date_format:H:i:s',
                'horario_lunes_fin' => 'nullable|date_format:H:i:s',
                'horario_martes_inicio' => 'nullable|date_format:H:i:s',
                'horario_martes_fin' => 'nullable|date_format:H:i:s',
                'horario_miercoles_inicio' => 'nullable|date_format:H:i:s',
                'horario_miercoles_fin' => 'nullable|date_format:H:i:s',
                'horario_jueves_inicio' => 'nullable|date_format:H:i:s',
                'horario_jueves_fin' => 'nullable|date_format:H:i:s',
                'horario_viernes_inicio' => 'nullable|date_format:H:i:s',
                'horario_viernes_fin' => 'nullable|date_format:H:i:s',
                'horario_sabado_inicio' => 'nullable|date_format:H:i:s',
                'horario_sabado_fin' => 'nullable|date_format:H:i:s',
                'horario_domingo_inicio' => 'nullable|date_format:H:i:s',
                'horario_domingo_fin' => 'nullable|date_format:H:i:s',
                'articulos' => 'nullable|array',
                'articulos.*' => 'nullable|string|exists:articulos,identificador_externo',
            ]);

            DB::beginTransaction();

            // Crear el proveedor
            $proveedor = Proveedor::create([
                'identificador_externo' => $validated['identificador_externo'],
                'nombre_comercial' => $validated['nombre_comercial'],
                'calendario_siempre_abierto' => $validated['calendario_siempre_abierto'],
                'horario_lunes_inicio' => $validated['horario_lunes_inicio'] ?? null,
                'horario_lunes_fin' => $validated['horario_lunes_fin'] ?? null,
                'horario_martes_inicio' => $validated['horario_martes_inicio'] ?? null,
                'horario_martes_fin' => $validated['horario_martes_fin'] ?? null,
                'horario_miercoles_inicio' => $validated['horario_miercoles_inicio'] ?? null,
                'horario_miercoles_fin' => $validated['horario_miercoles_fin'] ?? null,
                'horario_jueves_inicio' => $validated['horario_jueves_inicio'] ?? null,
                'horario_jueves_fin' => $validated['horario_jueves_fin'] ?? null,
                'horario_viernes_inicio' => $validated['horario_viernes_inicio'] ?? null,
                'horario_viernes_fin' => $validated['horario_viernes_fin'] ?? null,
                'horario_sabado_inicio' => $validated['horario_sabado_inicio'] ?? null,
                'horario_sabado_fin' => $validated['horario_sabado_fin'] ?? null,
                'horario_domingo_inicio' => $validated['horario_domingo_inicio'] ?? null,
                'horario_domingo_fin' => $validated['horario_domingo_fin'] ?? null,
            ]);

            Log::debug($prefix . "Proveedor creado con ID: " . $proveedor->id);

            // Asociar artículos si existen
            if ($request->has('articulos') && is_array($request->articulos)) {
                // Convertir identificadores externos a IDs internos
                $articuloIds = Articulo::whereIn('identificador_externo', $request->articulos)
                    ->pluck('id')
                    ->toArray();
                
                $proveedor->articulos()->sync($articuloIds);
                Log::debug($prefix . "Artículos asociados: " . count($articuloIds));
            }

            DB::commit();

            // Cargar relaciones
            $proveedor->load('articulos');

            // Transformar artículos para mostrar solo identificadores externos
            $proveedorArray = $proveedor->toArray();
            $proveedorArray['articulos'] = $proveedor->articulos->map(function ($articulo) {
                return [
                    'identificador_externo' => $articulo->identificador_externo,
                    'nombre' => $articulo->nombre,
                    'precio_base' => $articulo->precio_base,
                ];
            })->toArray();

            Log::debug($prefix . "Proveedor creado exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Proveedor creado exitosamente', $proveedorArray, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error($prefix . "Validation error:\n" . json_encode($e->errors(), JSON_PRETTY_PRINT));
            Log::error($prefix . "stop");
            return $this->error('Error en la validación de datos', 400, ['errors' => $e->errors()]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al crear el proveedor: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un proveedor específico con sus artículos.
     *
     * @OA\Get(
     *     path="/api/proveedores/{id}",
     *     tags={"ProveedorController"},
     *     summary="Obtener un proveedor específico",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del proveedor",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedor encontrado."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proveedor no encontrado."
     *     )
     * )
     */
    public function show($id)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "ID: " . $id);

        try {
            $proveedor = Proveedor::with('articulos')->find($id);

            if (!$proveedor) {
                Log::warning($prefix . "Proveedor no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Proveedor no encontrado', 404);
            }

            // Transformar artículos para mostrar solo identificadores externos
            $proveedorArray = $proveedor->toArray();
            $proveedorArray['articulos'] = $proveedor->articulos->map(function ($articulo) {
                return [
                    'identificador_externo' => $articulo->identificador_externo,
                    'nombre' => $articulo->nombre,
                    'precio_base' => $articulo->precio_base,
                ];
            })->toArray();

            Log::debug($prefix . "Proveedor encontrado");
            Log::debug($prefix . "stop");

            return $this->success('Proveedor encontrado', $proveedorArray);

        } catch (Throwable $e) {
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al obtener el proveedor: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @OA\Put(
     *     path="/api/proveedores/{id}",
     *     tags={"ProveedorController"},
     *     summary="Actualizar un proveedor existente",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del proveedor",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="identificador_externo", type="string", example="PROV-001"),
     *                 @OA\Property(property="nombre_comercial", type="string", example="Distribuidora ABC S.L."),
     *                 @OA\Property(property="calendario_siempre_abierto", type="boolean", example=false),
     *                 @OA\Property(property="horario_lunes_inicio", type="string", format="time", example="08:00:00"),
     *                 @OA\Property(property="horario_lunes_fin", type="string", format="time", example="20:00:00"),
     *                 @OA\Property(property="articulos[]", type="array", @OA\Items(type="string"), example={"ART-001", "ART-003"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedor actualizado exitosamente."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proveedor no encontrado."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "ID: " . $id);
        Log::debug($prefix . "request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));

        try {
            $proveedor = Proveedor::find($id);

            if (!$proveedor) {
                Log::warning($prefix . "Proveedor no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Proveedor no encontrado', 404);
            }

            // Validación de datos
            $validated = $request->validate([
                'identificador_externo' => 'sometimes|string|unique:proveedores,identificador_externo,' . $id,
                'nombre_comercial' => 'sometimes|string|max:255',
                'calendario_siempre_abierto' => 'sometimes|boolean',
                'horario_lunes_inicio' => 'nullable|date_format:H:i:s',
                'horario_lunes_fin' => 'nullable|date_format:H:i:s',
                'horario_martes_inicio' => 'nullable|date_format:H:i:s',
                'horario_martes_fin' => 'nullable|date_format:H:i:s',
                'horario_miercoles_inicio' => 'nullable|date_format:H:i:s',
                'horario_miercoles_fin' => 'nullable|date_format:H:i:s',
                'horario_jueves_inicio' => 'nullable|date_format:H:i:s',
                'horario_jueves_fin' => 'nullable|date_format:H:i:s',
                'horario_viernes_inicio' => 'nullable|date_format:H:i:s',
                'horario_viernes_fin' => 'nullable|date_format:H:i:s',
                'horario_sabado_inicio' => 'nullable|date_format:H:i:s',
                'horario_sabado_fin' => 'nullable|date_format:H:i:s',
                'horario_domingo_inicio' => 'nullable|date_format:H:i:s',
                'horario_domingo_fin' => 'nullable|date_format:H:i:s',
                'articulos' => 'nullable|array',
                'articulos.*' => 'nullable|string|exists:articulos,identificador_externo',
            ]);

            DB::beginTransaction();

            // Actualizar campos del proveedor
            $proveedor->update($request->only([
                'identificador_externo',
                'nombre_comercial',
                'calendario_siempre_abierto',
                'horario_lunes_inicio',
                'horario_lunes_fin',
                'horario_martes_inicio',
                'horario_martes_fin',
                'horario_miercoles_inicio',
                'horario_miercoles_fin',
                'horario_jueves_inicio',
                'horario_jueves_fin',
                'horario_viernes_inicio',
                'horario_viernes_fin',
                'horario_sabado_inicio',
                'horario_sabado_fin',
                'horario_domingo_inicio',
                'horario_domingo_fin',
            ]));

            Log::debug($prefix . "Proveedor actualizado");

            // Sincronizar artículos si se especifican
            if ($request->has('articulos')) {
                if (is_array($request->articulos) && count($request->articulos) > 0) {
                    // Convertir identificadores externos a IDs internos
                    $articuloIds = Articulo::whereIn('identificador_externo', $request->articulos)
                        ->pluck('id')
                        ->toArray();
                    
                    $proveedor->articulos()->sync($articuloIds);
                    Log::debug($prefix . "Artículos sincronizados: " . count($articuloIds));
                } else {
                    // Si artículos es null o vacío, desvincular todos
                    $proveedor->articulos()->sync([]);
                    Log::debug($prefix . "Todos los artículos desvinculados");
                }
            }

            DB::commit();

            // Recargar relaciones
            $proveedor->load('articulos');

            // Transformar artículos para mostrar solo identificadores externos
            $proveedorArray = $proveedor->toArray();
            $proveedorArray['articulos'] = $proveedor->articulos->map(function ($articulo) {
                return [
                    'identificador_externo' => $articulo->identificador_externo,
                    'nombre' => $articulo->nombre,
                    'precio_base' => $articulo->precio_base,
                ];
            })->toArray();

            Log::debug($prefix . "Proveedor actualizado exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Proveedor actualizado exitosamente', $proveedorArray);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error($prefix . "Validation error:\n" . json_encode($e->errors(), JSON_PRETTY_PRINT));
            Log::error($prefix . "stop");
            return $this->error('Error en la validación de datos', 400, ['errors' => $e->errors()]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al actualizar el proveedor: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar un proveedor.
     *
     * @OA\Delete(
     *     path="/api/proveedores/{id}",
     *     tags={"ProveedorController"},
     *     summary="Eliminar un proveedor",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del proveedor",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedor eliminado exitosamente."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proveedor no encontrado."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function destroy($id)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "ID: " . $id);

        try {
            $proveedor = Proveedor::find($id);

            if (!$proveedor) {
                Log::warning($prefix . "Proveedor no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Proveedor no encontrado', 404);
            }

            DB::beginTransaction();

            // Las relaciones en la tabla pivot se eliminarán automáticamente por cascade
            $proveedor->delete();

            DB::commit();

            Log::debug($prefix . "Proveedor eliminado exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Proveedor eliminado exitosamente');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al eliminar el proveedor: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Listar todos los proveedores.
     *
     * @OA\Get(
     *     path="/api/proveedores",
     *     tags={"ProveedorController"},
     *     summary="Listar todos los proveedores",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proveedores."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function index()
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");

        try {
            $proveedores = Proveedor::with('articulos')->get();

            // Transformar artículos para mostrar solo identificadores externos
            $proveedoresArray = $proveedores->map(function ($proveedor) {
                $proveedorArray = $proveedor->toArray();
                $proveedorArray['articulos'] = $proveedor->articulos->map(function ($articulo) {
                    return [
                        'identificador_externo' => $articulo->identificador_externo,
                        'nombre' => $articulo->nombre,
                        'precio_base' => $articulo->precio_base,
                    ];
                })->toArray();
                return $proveedorArray;
            })->toArray();

            Log::debug($prefix . "Total proveedores: " . $proveedores->count());
            Log::debug($prefix . "stop");

            return $this->success('Lista de proveedores', ['proveedores' => $proveedoresArray]);

        } catch (Throwable $e) {
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al obtener los proveedores: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verificar disponibilidad de un proveedor en un día y hora específicos.
     *
     * @OA\Get(
     *     path="/api/proveedores/{id}/disponibilidad",
     *     tags={"ProveedorController"},
     *     summary="Verificar disponibilidad de proveedor",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del proveedor",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         description="Día de la semana (lunes, martes, miercoles, jueves, viernes, sabado, domingo)",
     *         in="query",
     *         name="dia",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="Hora a verificar en formato HH:MM:SS",
     *         in="query",
     *         name="hora",
     *         required=true,
     *         @OA\Schema(type="string", format="time"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Disponibilidad verificada."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proveedor no encontrado."
     *     )
     * )
     */
    public function verificarDisponibilidad(Request $request, $id)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "ID: " . $id);

        try {
            $proveedor = Proveedor::find($id);

            if (!$proveedor) {
                Log::warning($prefix . "Proveedor no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Proveedor no encontrado', 404);
            }

            // Validar parámetros
            $validated = $request->validate([
                'dia' => 'required|string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
                'hora' => 'required|date_format:H:i:s',
            ]);

            $disponible = $proveedor->estaDisponible($validated['dia'], $validated['hora']);

            Log::debug($prefix . "Disponibilidad verificada: " . ($disponible ? 'SÍ' : 'NO'));
            Log::debug($prefix . "stop");

            return $this->success('Disponibilidad verificada', [
                'proveedor_id' => $proveedor->id,
                'identificador_externo' => $proveedor->identificador_externo,
                'nombre_comercial' => $proveedor->nombre_comercial,
                'dia' => $validated['dia'],
                'hora' => $validated['hora'],
                'disponible' => $disponible,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error($prefix . "Validation error:\n" . json_encode($e->errors(), JSON_PRETTY_PRINT));
            Log::error($prefix . "stop");
            return $this->error('Error en la validación de datos', 400, ['errors' => $e->errors()]);
        } catch (Throwable $e) {
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al verificar disponibilidad: ' . $e->getMessage(), 500);
        }
    }
}
