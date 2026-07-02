<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Album;
use App\Models\Archivo;
use App\Models\GaleriaActividad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $query = Album::with(['actividad:id,nombre,fecha_inicio,fecha_termino', 'creador:id,username'])
            ->withCount('fotos')
            ->latest('updated_at')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', "%{$search}%")
                    ->orWhereHas('actividad', fn ($activityQuery) => $activityQuery->where('nombre', 'like', "%{$search}%"));
            });
        }

        return response()->json($query->paginate(8), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'actividad_id' => ['required', 'integer', 'exists:actividades,id'],
            'creado_por' => ['nullable', 'integer'],
        ]);

        $data['creado_por'] = $request->user()?->id
            ?? User::query()->whereKey($data['creado_por'] ?? null)->value('id');

        $album = Album::create($data);

        return response()->json($album->load(['actividad', 'creador'])->loadCount('fotos'), 201);
    }

    public function show(Album $album)
    {
        return response()->json($this->loadAlbum($album), 200);
    }

    public function update(Request $request, Album $album)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'actividad_id' => ['sometimes', 'required', 'integer', 'exists:actividades,id'],
        ]);

        $album->update($data);

        return response()->json($this->loadAlbum($album), 200);
    }

    public function destroy(Request $request, Album $album)
    {
        if (! $this->canDeleteAlbum($request, $album)) {
            return response()->json(['message' => 'No tienes permisos para eliminar este album.'], 403);
        }

        foreach ($album->fotos()->get() as $foto) {
            $this->deletePhotoFile($foto);
        }

        $album->delete();

        return response()->json(null, 204);
    }

    public function uploadPhoto(Request $request, Album $album)
    {
        $data = $request->validate([
            'archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nombre' => ['nullable', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'subido_por' => ['nullable', 'integer'],
        ]);

        Storage::disk('public')->makeDirectory('albumes/'.$album->id);

        $file = $request->file('archivo');
        $path = $file->store('albumes/'.$album->id, 'public');
        $title = $data['nombre'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $uploaderId = $request->user()?->id
            ?? User::query()->whereKey($data['subido_por'] ?? null)->value('id');

        $archivo = Archivo::create([
            'entidad' => 'album',
            'entidad_id' => $album->id,
            'categoria' => 'foto_album',
            'ruta' => $path,
            'nombre_original' => $title,
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'tamano' => $file->getSize(),
            'descripcion' => $data['descripcion'] ?? null,
            'subido_por' => $uploaderId,
        ]);

        GaleriaActividad::create([
            'actividad_id' => $album->actividad_id,
            'archivo_id' => $archivo->id,
            'titulo' => $title,
            'descripcion' => $data['descripcion'] ?? null,
            'fecha' => now()->toDateString(),
            'subido_por' => $uploaderId,
        ]);

        return response()->json($this->loadPhoto($archivo), 201);
    }

    public function updatePhoto(Request $request, Album $album, Archivo $archivo)
    {
        $this->ensurePhotoBelongsToAlbum($album, $archivo);

        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $archivo->update([
            'nombre_original' => $data['nombre'] ?? $archivo->nombre_original,
            'descripcion' => $data['descripcion'] ?? $archivo->descripcion,
        ]);

        GaleriaActividad::where('archivo_id', $archivo->id)->update([
            'titulo' => $archivo->nombre_original,
            'descripcion' => $archivo->descripcion,
        ]);

        return response()->json($this->loadPhoto($archivo->fresh()), 200);
    }

    public function destroyPhoto(Request $request, Album $album, Archivo $archivo)
    {
        $this->ensurePhotoBelongsToAlbum($album, $archivo);

        if (! $this->canDeletePhoto($request, $archivo)) {
            return response()->json(['message' => 'No tienes permisos para eliminar esta foto.'], 403);
        }

        $this->deletePhotoFile($archivo);

        return response()->json(null, 204);
    }

    private function loadAlbum(Album $album): Album
    {
        return $album->load([
            'actividad:id,nombre,fecha_inicio,fecha_termino',
            'creador:id,username',
            'fotos.subidoPor.voluntario',
        ])->loadCount('fotos');
    }

    private function loadPhoto(Archivo $archivo): Archivo
    {
        return $archivo->load('subidoPor.voluntario');
    }

    private function ensurePhotoBelongsToAlbum(Album $album, Archivo $archivo): void
    {
        abort_unless(
            $archivo->entidad === 'album'
                && (int) $archivo->entidad_id === (int) $album->id
                && $archivo->categoria === 'foto_album',
            404
        );
    }

    private function deletePhotoFile(Archivo $archivo): void
    {
        GaleriaActividad::where('archivo_id', $archivo->id)->delete();

        if ($archivo->ruta) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        $archivo->delete();
    }

    private function canDeletePhoto(Request $request, Archivo $archivo): bool
    {
        $actor = $this->resolveActor($request);

        if (! $actor) {
            return false;
        }

        return $this->isAdministrator($actor) || (int) $archivo->subido_por === (int) $actor->id;
    }

    private function canDeleteAlbum(Request $request, Album $album): bool
    {
        $actor = $this->resolveActor($request);

        if (! $actor) {
            return false;
        }

        return $this->isAdministrator($actor) || (int) $album->creado_por === (int) $actor->id;
    }

    private function resolveActor(Request $request): ?User
    {
        if ($request->user()) {
            return $request->user()->loadMissing('roles');
        }

        $actorId = $request->integer('actor_id') ?: $request->integer('subido_por') ?: $request->integer('creado_por');

        return $actorId ? User::with('roles')->find($actorId) : null;
    }

    private function isAdministrator(User $user): bool
    {
        return $user->roles->contains(fn ($role) => $role->clave === 'administrador');
    }
}

