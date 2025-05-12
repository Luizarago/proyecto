<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Caracteristica;
use App\Models\Categoria;
use Exception;
use Illuminate\Support\Facades\DB;

class categoriaController extends Controller
{
    /**
    * Constructor del controlador.
    * Asigna permisos a cada acci�n usando middlewares.
    * As�, solo los usuarios con el permiso adecuado pueden ver, crear, editar o eliminar categor�as.
    */
    function __construct()
    {
        $this->middleware('permission:ver-categoria|crear-categoria|editar-categoria|eliminar-categoria', ['only' => ['index']]);
        $this->middleware('permission:crear-categoria', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-categoria', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-categoria', ['only' => ['destroy']]);
    }

    /**
     * Muestra el listado de todas las categor�as.
    * Obtiene todas las categor�as junto con su caracter�stica asociada, ordenadas de la m�s reciente a la m�s antigua.
    * Luego, env�a esos datos a la vista 'categoria.index' para mostrarlos al usuario.
    */
    public function index()
    {
        $categorias = Categoria::with('caracteristica')->latest()->get();

        return view('categoria.index', ['categorias' => $categorias]);
    }

    /**
    * Muestra el formulario para crear una nueva categor�a.
    * Simplemente devuelve la vista 'categoria.create' donde el usuario puede rellenar los datos.
    */
    public function create()
    {
        return view('categoria.create');
    }

    /**
    * Guarda una nueva categor�a en la base de datos.
    * Primero crea una caracter�stica con los datos validados del formulario.
    * Luego crea la categor�a asociada a esa caracter�stica.
    * Si todo va bien, guarda los cambios; si hay error, deshace la operaci�n.
    * Al finalizar, redirige al listado de categor�as con un mensaje de �xito.
    */
    public function store(StoreCaracteristicaRequest $request)
    {
        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->categoria()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        return redirect()->route('categorias.index')->with('success', 'Categoría registrada');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
    * Muestra el formulario para editar una categor�a existente.
    * Env�a los datos de la categor�a seleccionada a la vista 'categoria.edit' para que el usuario pueda modificarlos.
    */
    public function edit(Categoria $categoria)
    {
        return view('categoria.edit', ['categoria' => $categoria]);
    }

    /**
    * Actualiza la caracter�stica asociada a la categor�a seleccionada.
    * Usa los datos validados del formulario para modificar la caracter�stica.
    * Al terminar, redirige al listado de categor�as con un mensaje de �xito.
    */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        Caracteristica::where('id', $categoria->caracteristica->id)
            ->update($request->validated());

        return redirect()->route('categorias.index')->with('success', 'Categoría editada');
    }

    /**
    * Elimina o restaura una categor�a cambiando el estado de su caracter�stica.
    * Si la caracter�stica est� activa (estado 1), la desactiva (estado 0) y muestra mensaje de eliminada.
    * Si ya est� desactivada, la vuelve a activar (estado 1) y muestra mensaje de restaurada.
    * Al final, redirige al listado de categor�as con el mensaje correspondiente.
    */
    public function destroy(string $id)
    {
        $message = '';
        $categoria = Categoria::find($id);
        if ($categoria->caracteristica->estado == 1) {
            Caracteristica::where('id', $categoria->caracteristica->id)
                ->update([
                    'estado' => 0
                ]);
            $message = 'Categoría eliminada';
        } else {
            Caracteristica::where('id', $categoria->caracteristica->id)
                ->update([
                    'estado' => 1
                ]);
            $message = 'Categoría restaurada';
        }

        return redirect()->route('categorias.index')->with('success', $message);
    }
}
