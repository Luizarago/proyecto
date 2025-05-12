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
    * Asigna permisos a cada acción usando middlewares.
    * Así, solo los usuarios con el permiso adecuado pueden ver, crear, editar o eliminar categorías.
    */
    function __construct()
    {
        $this->middleware('permission:ver-categoria|crear-categoria|editar-categoria|eliminar-categoria', ['only' => ['index']]);
        $this->middleware('permission:crear-categoria', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-categoria', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-categoria', ['only' => ['destroy']]);
    }

    /**
     * Muestra el listado de todas las categorías.
    * Obtiene todas las categorías junto con su característica asociada, ordenadas de la más reciente a la más antigua.
    * Luego, envía esos datos a la vista 'categoria.index' para mostrarlos al usuario.
    */
    public function index()
    {
        $categorias = Categoria::with('caracteristica')->latest()->get();

        return view('categoria.index', ['categorias' => $categorias]);
    }

    /**
    * Muestra el formulario para crear una nueva categoría.
    * Simplemente devuelve la vista 'categoria.create' donde el usuario puede rellenar los datos.
    */
    public function create()
    {
        return view('categoria.create');
    }

    /**
    * Guarda una nueva categoría en la base de datos.
    * Primero crea una característica con los datos validados del formulario.
    * Luego crea la categoría asociada a esa característica.
    * Si todo va bien, guarda los cambios; si hay error, deshace la operación.
    * Al finalizar, redirige al listado de categorías con un mensaje de éxito.
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

        return redirect()->route('categorias.index')->with('success', 'CategorÃ­a registrada');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
    * Muestra el formulario para editar una categoría existente.
    * Envía los datos de la categoría seleccionada a la vista 'categoria.edit' para que el usuario pueda modificarlos.
    */
    public function edit(Categoria $categoria)
    {
        return view('categoria.edit', ['categoria' => $categoria]);
    }

    /**
    * Actualiza la característica asociada a la categoría seleccionada.
    * Usa los datos validados del formulario para modificar la característica.
    * Al terminar, redirige al listado de categorías con un mensaje de éxito.
    */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        Caracteristica::where('id', $categoria->caracteristica->id)
            ->update($request->validated());

        return redirect()->route('categorias.index')->with('success', 'CategorÃ­a editada');
    }

    /**
    * Elimina o restaura una categoría cambiando el estado de su característica.
    * Si la característica está activa (estado 1), la desactiva (estado 0) y muestra mensaje de eliminada.
    * Si ya está desactivada, la vuelve a activar (estado 1) y muestra mensaje de restaurada.
    * Al final, redirige al listado de categorías con el mensaje correspondiente.
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
            $message = 'CategorÃ­a eliminada';
        } else {
            Caracteristica::where('id', $categoria->caracteristica->id)
                ->update([
                    'estado' => 1
                ]);
            $message = 'CategorÃ­a restaurada';
        }

        return redirect()->route('categorias.index')->with('success', $message);
    }
}
