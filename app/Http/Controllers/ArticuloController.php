<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Imagen;
use App\Models\Documento;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @OA\Tag(
 *     name="ArticuloController",
 *     description="Operaciones sobre artículos"
 * )
 */
class ArticuloController extends Controller
{
    /**
     * Crear un nuevo artículo con imágenes y documentos asociados.
     *
     * @OA\Post(
     *     path="/api/articulos",
     *     tags={"ArticuloController"},
     *     summary="Crear un nuevo artículo",
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="identificador_externo", type="string", example="ART-001"),
     *                 @OA\Property(property="nombre", type="string", example="Producto de ejemplo"),
     *                 @OA\Property(property="descripcion", type="string", example="Descripción detallada"),
     *                 @OA\Property(property="precio_base", type="number", format="float", example=100.50),
     *                 @OA\Property(property="tipo_iva", type="string", example="general"),
     *                 @OA\Property(property="porcentaje_iva", type="number", format="float", example=21.00),
     *                 @OA\Property(property="imagenes[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="documentos[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="proveedores[]", type="array", @OA\Items(type="string"), example={"PROV-001", "PROV-002", "PROV-003"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artículo creado exitosamente."
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
        Log::debug($prefix . "request:\n" . json_encode($request->except(['imagenes', 'documentos']), JSON_PRETTY_PRINT));

        try {
            // Validación de datos
            $validated = $request->validate([
                'identificador_externo' => 'required|string|unique:articulos,identificador_externo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'precio_base' => 'required|numeric|min:0',
                'tipo_iva' => 'required|string|in:general,reducido,superreducido,exento',
                'porcentaje_iva' => 'required|numeric|min:0|max:100',
                'imagenes.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'documentos.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:10240',
                'proveedores' => 'nullable|array',
                'proveedores.*' => 'nullable|string|exists:proveedores,identificador_externo',
            ]);

            DB::beginTransaction();

            // Crear el artículo
            $articulo = Articulo::create([
                'identificador_externo' => $validated['identificador_externo'],
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_base' => $validated['precio_base'],
                'tipo_iva' => $validated['tipo_iva'],
                'porcentaje_iva' => $validated['porcentaje_iva'],
            ]);

            Log::debug($prefix . "Artículo creado con ID: " . $articulo->id);

            // Procesar imágenes si existen
            if ($request->hasFile('imagenes')) {
                $this->storeImagenes($request->file('imagenes'), $articulo);
            }

            // Procesar documentos si existen
            if ($request->hasFile('documentos')) {
                $this->storeDocumentos($request->file('documentos'), $articulo);
            }

            // Asociar proveedores si existen
            if ($request->has('proveedores') && is_array($request->proveedores)) {
                // Convertir identificadores externos a IDs internos
                $proveedorIds = Proveedor::whereIn('identificador_externo', $request->proveedores)
                    ->pluck('id')
                    ->toArray();
                
                $articulo->proveedores()->sync($proveedorIds);
                Log::debug($prefix . "Proveedores asociados: " . count($proveedorIds));
            }

            DB::commit();

            // Cargar relaciones
            $articulo->load(['imagenes', 'documentos', 'proveedores']);

            // Transformar proveedores para mostrar solo identificadores externos
            $articuloArray = $articulo->toArray();
            $articuloArray['proveedores'] = $articulo->proveedores->map(function ($proveedor) {
                return [
                    'identificador_externo' => $proveedor->identificador_externo,
                    'nombre_comercial' => $proveedor->nombre_comercial,
                    'calendario_siempre_abierto' => $proveedor->calendario_siempre_abierto,
                ];
            })->toArray();

            Log::debug($prefix . "Artículo creado exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Artículo creado exitosamente', $articuloArray, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error($prefix . "Validation error:\n" . json_encode($e->errors(), JSON_PRETTY_PRINT));
            Log::error($prefix . "stop");
            return $this->error('Error en la validación de datos', 400, ['errors' => $e->errors()]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al crear el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un artículo específico con sus imágenes y documentos.
     *
     * @OA\Get(
     *     path="/api/articulos/{id}",
     *     tags={"ArticuloController"},
     *     summary="Obtener un artículo específico",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del artículo",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo encontrado."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artículo no encontrado."
     *     )
     * )
     */
    public function show($id)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . "start");
        Log::debug($prefix . "ID: " . $id);

        try {
            $articulo = Articulo::with(['imagenes', 'documentos', 'proveedores'])->find($id);

            if (!$articulo) {
                Log::warning($prefix . "Artículo no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Artículo no encontrado', 404);
            }

            // Transformar proveedores para mostrar solo identificadores externos
            $articuloArray = $articulo->toArray();
            $articuloArray['proveedores'] = $articulo->proveedores->map(function ($proveedor) {
                return [
                    'identificador_externo' => $proveedor->identificador_externo,
                    'nombre_comercial' => $proveedor->nombre_comercial,
                    'calendario_siempre_abierto' => $proveedor->calendario_siempre_abierto,
                ];
            })->toArray();

            Log::debug($prefix . "Artículo encontrado");
            Log::debug($prefix . "stop");

            return $this->success('Artículo encontrado', $articuloArray);

        } catch (Throwable $e) {
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al obtener el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar un artículo existente.
     *
     * @OA\Post(
     *     path="/api/articulos/{id}",
     *     tags={"ArticuloController"},
     *     summary="Actualizar un artículo existente",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del artículo",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="identificador_externo", type="string", example="ART-001"),
     *                 @OA\Property(property="nombre", type="string", example="Producto actualizado"),
     *                 @OA\Property(property="descripcion", type="string", example="Nueva descripción"),
     *                 @OA\Property(property="precio_base", type="number", format="float", example=150.75),
     *                 @OA\Property(property="tipo_iva", type="string", example="reducido"),
     *                 @OA\Property(property="porcentaje_iva", type="number", format="float", example=10.00),
     *                 @OA\Property(property="imagenes[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="documentos[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="proveedores[]", type="array", @OA\Items(type="string"), example={"PROV-001", "PROV-002"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo actualizado exitosamente."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artículo no encontrado."
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
        Log::debug($prefix . "request:\n" . json_encode($request->except(['imagenes', 'documentos']), JSON_PRETTY_PRINT));

        try {
            $articulo = Articulo::find($id);

            if (!$articulo) {
                Log::warning($prefix . "Artículo no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Artículo no encontrado', 404);
            }

            // Validación de datos
            $validated = $request->validate([
                'identificador_externo' => 'sometimes|string|unique:articulos,identificador_externo,' . $id,
                'nombre' => 'sometimes|string|max:255',
                'descripcion' => 'nullable|string',
                'precio_base' => 'sometimes|numeric|min:0',
                'tipo_iva' => 'sometimes|string|in:general,reducido,superreducido,exento',
                'porcentaje_iva' => 'sometimes|numeric|min:0|max:100',
                'imagenes.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'documentos.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:10240',
                'proveedores' => 'nullable|array',
                'proveedores.*' => 'nullable|string|exists:proveedores,identificador_externo',
            ]);

            DB::beginTransaction();

            // Actualizar campos del artículo
            $articulo->update($request->only([
                'identificador_externo',
                'nombre',
                'descripcion',
                'precio_base',
                'tipo_iva',
                'porcentaje_iva'
            ]));

            Log::debug($prefix . "Artículo actualizado");

            // Si se envían nuevas imágenes, eliminar las antiguas
            if ($request->hasFile('imagenes')) {
                $this->deleteAllImagenes($articulo);
                $this->storeImagenes($request->file('imagenes'), $articulo);
                Log::debug($prefix . "Imágenes reemplazadas");
            }

            // Si se envían nuevos documentos, eliminar los antiguos
            if ($request->hasFile('documentos')) {
                $this->deleteAllDocumentos($articulo);
                $this->storeDocumentos($request->file('documentos'), $articulo);
                Log::debug($prefix . "Documentos reemplazados");
            }

            // Sincronizar proveedores si se especifican
            if ($request->has('proveedores')) {
                if (is_array($request->proveedores) && count($request->proveedores) > 0) {
                    // Convertir identificadores externos a IDs internos
                    $proveedorIds = Proveedor::whereIn('identificador_externo', $request->proveedores)
                        ->pluck('id')
                        ->toArray();
                    
                    $articulo->proveedores()->sync($proveedorIds);
                    Log::debug($prefix . "Proveedores sincronizados: " . count($proveedorIds));
                } else {
                    // Si proveedores es null o vacío, desvincular todos
                    $articulo->proveedores()->sync([]);
                    Log::debug($prefix . "Todos los proveedores desvinculados");
                }
            }

            DB::commit();

            // Recargar relaciones
            $articulo->load(['imagenes', 'documentos', 'proveedores']);

            // Transformar proveedores para mostrar solo identificadores externos
            $articuloArray = $articulo->toArray();
            $articuloArray['proveedores'] = $articulo->proveedores->map(function ($proveedor) {
                return [
                    'identificador_externo' => $proveedor->identificador_externo,
                    'nombre_comercial' => $proveedor->nombre_comercial,
                    'calendario_siempre_abierto' => $proveedor->calendario_siempre_abierto,
                ];
            })->toArray();

            Log::debug($prefix . "Artículo actualizado exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Artículo actualizado exitosamente', $articuloArray);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error($prefix . "Validation error:\n" . json_encode($e->errors(), JSON_PRETTY_PRINT));
            Log::error($prefix . "stop");
            return $this->error('Error en la validación de datos', 400, ['errors' => $e->errors()]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al actualizar el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar un artículo y sus archivos asociados.
     *
     * @OA\Delete(
     *     path="/api/articulos/{id}",
     *     tags={"ArticuloController"},
     *     summary="Eliminar un artículo",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         description="ID del artículo",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo eliminado exitosamente."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artículo no encontrado."
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
            $articulo = Articulo::with(['imagenes', 'documentos'])->find($id);

            if (!$articulo) {
                Log::warning($prefix . "Artículo no encontrado");
                Log::warning($prefix . "stop");
                return $this->error('Artículo no encontrado', 404);
            }

            DB::beginTransaction();

            // Eliminar todas las imágenes y sus archivos
            $this->deleteAllImagenes($articulo);

            // Eliminar todos los documentos y sus archivos
            $this->deleteAllDocumentos($articulo);

            // Eliminar el artículo
            $articulo->delete();

            DB::commit();

            Log::debug($prefix . "Artículo y archivos eliminados exitosamente");
            Log::debug($prefix . "stop");

            return $this->success('Artículo eliminado exitosamente');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al eliminar el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Listar todos los artículos.
     *
     * @OA\Get(
     *     path="/api/articulos",
     *     tags={"ArticuloController"},
     *     summary="Listar todos los artículos",
     *     @OA\Parameter(
     *         description="Token JWT",
     *         in="header",
     *         name="Authorization",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de artículos."
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
            $articulos = Articulo::with(['imagenes', 'documentos', 'proveedores'])->get();

            // Transformar proveedores para mostrar solo identificadores externos
            $articulosArray = $articulos->map(function ($articulo) {
                $articuloArray = $articulo->toArray();
                $articuloArray['proveedores'] = $articulo->proveedores->map(function ($proveedor) {
                    return [
                        'identificador_externo' => $proveedor->identificador_externo,
                        'nombre_comercial' => $proveedor->nombre_comercial,
                        'calendario_siempre_abierto' => $proveedor->calendario_siempre_abierto,
                    ];
                })->toArray();
                return $articuloArray;
            })->toArray();

            Log::debug($prefix . "Total artículos: " . $articulos->count());
            Log::debug($prefix . "stop");

            return $this->success('Lista de artículos', ['articulos' => $articulosArray]);

        } catch (Throwable $e) {
            Log::error($prefix . "exception:\n" . $e->getMessage());
            Log::error($prefix . "stop");
            return $this->error('Error al obtener los artículos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Almacenar imágenes en el sistema de archivos y base de datos.
     *
     * @param array $imagenes
     * @param Articulo $articulo
     * @return void
     */
    private function storeImagenes(array $imagenes, Articulo $articulo)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        
        foreach ($imagenes as $index => $imagen) {
            $nombreOriginal = $imagen->getClientOriginalName();
            $extension = $imagen->getClientOriginalExtension();
            $nombreArchivo = time() . '_' . $index . '_' . uniqid() . '.' . $extension;
            
            // Guardar el archivo en storage/app/public/articulos/imagenes/{articulo_id}
            $ruta = $imagen->storeAs(
                'public/articulos/imagenes/' . $articulo->id,
                $nombreArchivo
            );

            // Guardar información en la base de datos
            Imagen::create([
                'articulo_id' => $articulo->id,
                'nombre_archivo' => $nombreOriginal,
                'ruta' => $ruta,
                'tipo_mime' => $imagen->getMimeType(),
                'tamanio' => $imagen->getSize(),
                'orden' => $index,
            ]);

            Log::debug($prefix . "Imagen guardada: " . $nombreArchivo);
        }
    }

    /**
     * Almacenar documentos en el sistema de archivos y base de datos.
     *
     * @param array $documentos
     * @param Articulo $articulo
     * @return void
     */
    private function storeDocumentos(array $documentos, Articulo $articulo)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        
        foreach ($documentos as $index => $documento) {
            $nombreOriginal = $documento->getClientOriginalName();
            $extension = $documento->getClientOriginalExtension();
            $nombreArchivo = time() . '_' . $index . '_' . uniqid() . '.' . $extension;
            
            // Guardar el archivo en storage/app/public/articulos/documentos/{articulo_id}
            $ruta = $documento->storeAs(
                'public/articulos/documentos/' . $articulo->id,
                $nombreArchivo
            );

            // Guardar información en la base de datos
            Documento::create([
                'articulo_id' => $articulo->id,
                'nombre_archivo' => $nombreOriginal,
                'ruta' => $ruta,
                'tipo_mime' => $documento->getMimeType(),
                'tamanio' => $documento->getSize(),
            ]);

            Log::debug($prefix . "Documento guardado: " . $nombreArchivo);
        }
    }

    /**
     * Eliminar todas las imágenes de un artículo.
     *
     * @param Articulo $articulo
     * @return void
     */
    private function deleteAllImagenes(Articulo $articulo)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        
        foreach ($articulo->imagenes as $imagen) {
            // Eliminar archivo físico
            if (Storage::exists($imagen->ruta)) {
                Storage::delete($imagen->ruta);
                Log::debug($prefix . "Archivo de imagen eliminado: " . $imagen->ruta);
            }
            
            // Eliminar registro de base de datos
            $imagen->delete();
        }

        // Intentar eliminar el directorio si está vacío
        $directorioImagenes = 'public/articulos/imagenes/' . $articulo->id;
        if (Storage::exists($directorioImagenes)) {
            Storage::deleteDirectory($directorioImagenes);
            Log::debug($prefix . "Directorio de imágenes eliminado");
        }
    }

    /**
     * Eliminar todos los documentos de un artículo.
     *
     * @param Articulo $articulo
     * @return void
     */
    private function deleteAllDocumentos(Articulo $articulo)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        
        foreach ($articulo->documentos as $documento) {
            // Eliminar archivo físico
            if (Storage::exists($documento->ruta)) {
                Storage::delete($documento->ruta);
                Log::debug($prefix . "Archivo de documento eliminado: " . $documento->ruta);
            }
            
            // Eliminar registro de base de datos
            $documento->delete();
        }

        // Intentar eliminar el directorio si está vacío
        $directorioDocumentos = 'public/articulos/documentos/' . $articulo->id;
        if (Storage::exists($directorioDocumentos)) {
            Storage::deleteDirectory($directorioDocumentos);
            Log::debug($prefix . "Directorio de documentos eliminado");
        }
    }
}
