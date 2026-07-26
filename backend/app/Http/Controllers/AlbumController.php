<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Album;
use App\Models\Archivo;
use App\Models\GaleriaActividad;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    public function index(Request $request)
    {
        $actor = $this->manager($request);
        $query = Album::with(['actividad:id,nombre,fecha_inicio,fecha_termino', 'creador:id,username'])
            ->withCount('fotos')
            ->latest('updated_at')
            ->latest('id');

        $filialId = (int) ($actor->voluntario?->filial_id ?? 0);
        if ($filialId > 0) {
            $query->whereHas('actividad', fn ($activityQuery) => $activityQuery->where('filial_id', $filialId));
        }

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

        $actor = $this->manager($request);
        $this->ensureManagerFilial($actor, Actividad::findOrFail($data['actividad_id']));
        $data['creado_por'] = $actor->id;

        $album = Album::create($data);

        return response()->json($album->load(['actividad', 'creador'])->loadCount('fotos'), 201);
    }

    public function show(Album $album, Request $request)
    {
        $this->ensureCanViewAlbum($request, $album);
        return response()->json($this->loadAlbum($album), 200);
    }

    public function update(Request $request, Album $album)
    {
        $actor = $this->manager($request);
        $this->ensureManagerFilial($actor, $album->actividad);
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'actividad_id' => ['sometimes', 'required', 'integer', 'exists:actividades,id'],
        ]);

        if (isset($data['actividad_id'])) {
            $this->ensureManagerFilial($actor, Actividad::findOrFail($data['actividad_id']));
        }
        $album->update($data);

        return response()->json($this->loadAlbum($album), 200);
    }

    public function destroy(Request $request, Album $album)
    {
        $this->ensureCanManageAlbum($request, $album);

        foreach ($album->fotos()->get() as $foto) {
            $this->deletePhotoFile($foto);
        }

        $album->delete();

        return response()->json(null, 204);
    }

    public function uploadPhoto(Request $request, Album $album)
    {
        $this->ensureCanManageAlbum($request, $album);
        $data = $request->validate([
            'archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'nombre' => ['nullable', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'subido_por' => ['nullable', 'integer'],
        ]);

        Storage::disk('public')->makeDirectory('albumes/'.$album->id);

        $file = $request->file('archivo');
        $optimized = $this->imageOptimizer->store($file, 'albumes/'.$album->id);
        $title = $data['nombre'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $uploaderId = $request->user()?->id
            ?? User::query()->whereKey($data['subido_por'] ?? null)->value('id');

        $archivo = Archivo::create([
            'entidad' => 'album',
            'entidad_id' => $album->id,
            'categoria' => 'foto_album',
            'ruta' => $optimized['path'],
            'nombre_original' => $title,
            'extension' => $optimized['extension'],
            'mime_type' => $optimized['mime_type'],
            'tamano' => $optimized['size'],
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
        $this->ensureCanManageAlbum($request, $album);

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

    public function downloadPhoto(Request $request, Album $album, Archivo $archivo)
    {
        $this->ensurePhotoBelongsToAlbum($album, $archivo);

        $this->ensureCanViewAlbum($request, $album);

        abort_unless($archivo->ruta && Storage::disk('public')->exists($archivo->ruta), 404);

        $extension = $archivo->extension ?: pathinfo($archivo->ruta, PATHINFO_EXTENSION);
        $baseName = pathinfo($archivo->nombre_original ?: 'fotografia', PATHINFO_FILENAME);
        $safeName = preg_replace('/[^\pL\pN._-]+/u', '-', $baseName) ?: 'fotografia';

        return Storage::disk('public')->download(
            $archivo->ruta,
            $safeName.'.'.$extension,
            ['Content-Type' => $archivo->mime_type ?: 'application/octet-stream']
        );
    }

    public function destroyPhoto(Request $request, Album $album, Archivo $archivo)
    {
        $this->ensurePhotoBelongsToAlbum($album, $archivo);

        $this->ensureCanManageAlbum($request, $album);
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

    private function ensureCanManageAlbum(Request $request, Album $album): void
    {
        $actor = $this->manager($request);
        $this->ensureManagerFilial($actor, $album->actividad);
    }

    private function ensureCanViewAlbum(Request $request, Album $album): void
    {
        $actor = $this->resolveActor($request);
        abort_unless($actor, 403);

        if ($this->isManager($actor)) {
            $this->ensureManagerFilial($actor, $album->actividad);
            return;
        }

        $volunteerId = (int) ($actor->voluntario?->id ?? 0);
        abort_unless(
            $volunteerId > 0
                && $album->actividad->voluntarios()->where('voluntarios.id', $volunteerId)->exists(),
            403,
            'No tienes permisos para acceder a este álbum.'
        );
    }

    private function manager(Request $request): User
    {
        $actor = $this->resolveActor($request);
        abort_unless($actor && $this->isManager($actor), 403, 'No tienes permisos para administrar álbumes.');
        return $actor;
    }

    private function ensureManagerFilial(User $actor, Actividad $activity): void
    {
        $filialId = (int) ($actor->voluntario?->filial_id ?? 0);
        if ($filialId > 0) {
            abort_unless($filialId === (int) $activity->filial_id, 403, 'No puedes administrar álbumes de otra filial.');
        }
    }

    private function resolveActor(Request $request): ?User
    {
        return $request->user()?->loadMissing('roles', 'voluntario');
    }

    private function isManager(User $user): bool
    {
        return $user->roles->contains(fn ($role) => in_array($role->clave, ['administrador', 'moderador'], true));
    }
}
