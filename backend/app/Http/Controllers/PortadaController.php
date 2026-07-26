<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Archivo;
use App\Models\CarruselInicio;
use App\Models\Novedad;
use App\Models\PortadaAjuste;
use App\Models\Voluntario;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortadaController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer) {}

    private const CAMPOS = ['objetivo', 'fecha', 'lugar', 'voluntarios', 'personas_ayudadas', 'filial', 'tipo'];

    public function show()
    {
        if (! Schema::hasTable('carrusel_inicio') || ! Schema::hasColumn('novedades', 'campos_visibles')) {
            return response()->json(['carrusel' => [], 'novedades' => []]);
        }

        return response()->json([
            'textos' => $this->publicSettings(),
            'carrusel' => CarruselInicio::with('imagenes')->where('publicada', true)->orderBy('orden')->get(),
            'novedades' => Novedad::with([
                'archivoPortada',
                'actividad.filial',
                'actividad.voluntarios',
            ])->where('publicada', true)->orderBy('orden')->get(),
        ]);
    }

    public function options(Request $request)
    {
        $this->ensureAdministrator($request);

        $activities = Actividad::with(['filial:id,nombre', 'galeria.archivo', 'albumes.fotos'])
            ->withCount('voluntarios')->orderByDesc('fecha_inicio')->orderByDesc('id')->get();

        $images = $activities->flatMap(function ($activity) {
            $gallery = $activity->galeria->filter(fn ($item) => $item->archivo)->map(fn ($item) => [
                'id' => $item->archivo->id,
                'url_publica' => $item->archivo->url_publica,
                'nombre_original' => $item->titulo ?: $item->archivo->nombre_original,
                'actividad_id' => $activity->id,
                'actividad_nombre' => $activity->nombre,
                'album_nombre' => 'Galería de la actividad',
                'origen' => 'galeria',
            ]);

            $albums = $activity->albumes->flatMap(fn ($album) => $album->fotos->map(fn ($file) => [
                'id' => $file->id,
                'url_publica' => $file->url_publica,
                'nombre_original' => $file->nombre_original,
                'actividad_id' => $activity->id,
                'actividad_nombre' => $activity->nombre,
                'album_nombre' => $album->nombre,
                'origen' => 'album',
            ]));

            return $gallery->concat($albums)->unique('id');
        })->values();

        $uploadedImages = Archivo::query()
            ->where('entidad', 'portada')
            ->where('categoria', 'imagen_portada')
            ->latest('id')
            ->get()
            ->map(fn ($file) => $this->portadaImageData($file));

        $images = $uploadedImages->concat($images)->unique('id')->values();

        return response()->json([
            'actividades' => $activities->map(fn ($activity) => [
                'id' => $activity->id,
                'nombre' => $activity->nombre,
                'tipo' => $activity->tipo,
                'objetivo' => $activity->objetivo,
                'fecha_inicio' => optional($activity->fecha_inicio)->toDateString(),
                'fecha_termino' => optional($activity->fecha_termino)->toDateString(),
                'lugar' => $activity->lugar,
                'filial' => $activity->filial,
                'voluntarios_count' => $activity->voluntarios_count,
                'imagenes' => $images->where('actividad_id', $activity->id)->values(),
            ]),
            'imagenes' => $images,
            'voluntarios_directorio' => Voluntario::with('archivoFotoPerfil')->orderBy('nombres')->orderBy('apellidos')->get()->map(fn ($volunteer) => [
                'id' => $volunteer->id,
                'nombre' => trim($volunteer->nombres.' '.$volunteer->apellidos),
                'email' => $volunteer->correo_electronico,
                'foto_url' => $volunteer->foto_perfil_url,
            ]),
            'configuracion' => $this->show()->getData(true),
        ]);
    }

    public function uploadImages(Request $request)
    {
        $this->ensureAdministrator($request);

        $data = $request->validate([
            'archivos' => ['required', 'array', 'min:1', 'max:10'],
            'archivos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        Storage::disk('public')->makeDirectory('portada');

        $images = collect($data['archivos'])->map(function ($file) use ($request) {
            $optimized = $this->imageOptimizer->store($file, 'portada');
            $archivo = Archivo::create([
                'entidad' => 'portada',
                'entidad_id' => $request->user()->id,
                'categoria' => 'imagen_portada',
                'ruta' => $optimized['path'],
                'nombre_original' => $optimized['original_name'],
                'extension' => $optimized['extension'],
                'mime_type' => $optimized['mime_type'],
                'tamano' => $optimized['size'],
                'subido_por' => $request->user()->id,
            ]);

            return $this->portadaImageData($archivo);
        });

        return response()->json(['imagenes' => $images], 201);
    }

    public function update(Request $request)
    {
        $this->ensureAdministrator($request);

        $data = $request->validate([
            'textos.carrusel_etiqueta' => ['nullable', 'string', 'max:120'],
            'textos.novedades_etiqueta' => ['nullable', 'string', 'max:120'],
            'textos.novedades_titulo' => ['nullable', 'string', 'max:180'],
            'textos.novedades_descripcion' => ['nullable', 'string', 'max:300'],
            'textos.carrusel_texto_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textos.carrusel_etiqueta_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textos.novedades_etiqueta_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textos.novedades_titulo_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textos.novedades_descripcion_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textos.telefono' => ['nullable', 'string', 'max:40'],
            'textos.whatsapp' => ['nullable', 'string', 'max:40'],
            'textos.correo_contacto' => ['nullable', 'email', 'max:255'],
            'textos.horario_atencion' => ['nullable', 'string', 'max:500'],
            'textos.instagram_url' => ['nullable', 'url', 'max:500'],
            'textos.facebook_url' => ['nullable', 'url', 'max:500'],
            'textos.direccion' => ['nullable', 'string', 'max:300'],
            'textos.ubicacion_url' => ['nullable', 'url', 'max:700'],
            'textos.directorio' => ['array', 'max:12'],
            'textos.directorio.*.voluntario_id' => ['required', 'integer', 'distinct', 'exists:voluntarios,id'],
            'textos.directorio.*.cargo' => ['nullable', 'string', 'max:120'],
            'textos.enlaces_relacionados' => ['array', 'max:12'],
            'textos.enlaces_relacionados.*.nombre' => ['nullable', 'string', 'max:120'],
            'textos.enlaces_relacionados.*.url' => ['nullable', 'url', 'max:500'],
            'novedades' => ['array'],
            'novedades.*.actividad_id' => ['required', 'integer', 'exists:actividades,id'],
            'novedades.*.archivo_portada_id' => ['nullable', 'integer', 'exists:archivos,id'],
            'novedades.*.posicion_x' => ['required', 'integer', 'between:0,100'],
            'novedades.*.posicion_y' => ['required', 'integer', 'between:0,100'],
            'novedades.*.zoom' => ['required', 'integer', 'between:100,250'],
            'novedades.*.titulo' => ['nullable', 'string', 'max:180'],
            'novedades.*.resumen' => ['nullable', 'string', 'max:1200'],
            'novedades.*.contenido' => ['nullable', 'string', 'max:5000'],
            'novedades.*.ancho' => ['required', Rule::in(['tercio', 'mitad', 'completo'])],
            'novedades.*.campos_visibles' => ['array'],
            'novedades.*.campos_visibles.*' => [Rule::in(self::CAMPOS)],
            'novedades.*.personas_ayudadas' => ['nullable', 'integer', 'min:0'],
            'novedades.*.publicada' => ['boolean'],
            'carrusel' => ['array', 'max:5'],
            'carrusel.*.titulo' => ['nullable', 'string', 'max:180'],
            'carrusel.*.titulo_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'carrusel.*.bajada' => ['nullable', 'string', 'max:800'],
            'carrusel.*.bajada_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'carrusel.*.publicada' => ['boolean'],
            'carrusel.*.imagenes' => ['required', 'array', 'min:1', 'max:3'],
            'carrusel.*.imagenes.*' => ['integer', 'distinct', 'exists:archivos,id'],
            'carrusel.*.posiciones_y' => ['required', 'array'],
            'carrusel.*.posiciones_y.*' => ['integer', 'between:0,100'],
            'carrusel.*.posiciones_x' => ['required', 'array'],
            'carrusel.*.posiciones_x.*' => ['integer', 'between:0,100'],
            'carrusel.*.zooms' => ['required', 'array'],
            'carrusel.*.zooms.*' => ['integer', 'between:100,250'],
        ]);

        foreach (['carrusel_etiqueta', 'novedades_etiqueta', 'novedades_titulo', 'novedades_descripcion'] as $field) {
            $data['textos'][$field] = $data['textos'][$field] ?? '';
        }

        $data['textos']['enlaces_relacionados'] = collect($data['textos']['enlaces_relacionados'] ?? [])
            ->filter(fn ($link) => filled($link['nombre'] ?? null) || filled($link['url'] ?? null))
            ->values()
            ->all();

        DB::transaction(function () use ($data, $request) {
            PortadaAjuste::updateOrCreate(['id' => 1], $data['textos']);
            Novedad::query()->delete();
            foreach ($data['novedades'] ?? [] as $order => $item) {
                $title = $item['titulo'] ?? '';
                Novedad::create([
                    ...$item,
                    'titulo' => $title,
                    'slug' => (Str::slug($title) ?: 'novedad').'-'.($order + 1),
                    'orden' => $order,
                    'creado_por' => $request->user()->id,
                ]);
            }

            CarruselInicio::query()->delete();
            foreach ($data['carrusel'] ?? [] as $order => $item) {
                $slide = CarruselInicio::create([
                    'titulo' => $item['titulo'] ?? null,
                    'titulo_color' => $item['titulo_color'],
                    'bajada' => $item['bajada'] ?? null,
                    'bajada_color' => $item['bajada_color'],
                    'orden' => $order,
                    'publicada' => $item['publicada'] ?? true,
                    'creado_por' => $request->user()->id,
                ]);
                $slide->imagenes()->sync(collect($item['imagenes'])->mapWithKeys(
                    fn ($id, $imageOrder) => [$id => [
                        'orden' => $imageOrder,
                        'posicion_x' => $item['posiciones_x'][$imageOrder] ?? 50,
                        'posicion_y' => $item['posiciones_y'][$imageOrder] ?? 50,
                        'zoom' => $item['zooms'][$imageOrder] ?? 100,
                    ]]
                ));
            }
        });

        return $this->show();
    }

    private function portadaImageData(Archivo $file): array
    {
        return [
            'id' => $file->id,
            'url_publica' => $file->url_publica,
            'nombre_original' => $file->nombre_original,
            'actividad_id' => null,
            'actividad_nombre' => 'Imágenes de portada',
            'album_nombre' => 'Cargadas desde el dispositivo',
            'origen' => 'dispositivo',
        ];
    }

    private function ensureAdministrator(Request $request): void
    {
        $user = $request->user()?->loadMissing('roles');
        abort_unless($user?->canManagePlatform(), 403, 'No tienes permisos para configurar la portada.');
    }

    private function defaultTexts(): array
    {
        return [
            'carrusel_etiqueta' => 'Historias que nos unen',
            'novedades_etiqueta' => 'Actualidad de nuestra comunidad',
            'novedades_titulo' => 'Novedades de Cruz Roja',
            'novedades_descripcion' => 'Conoce las actividades y el impacto de nuestros voluntarios.',
            'carrusel_texto_color' => '#ffffff',
            'carrusel_etiqueta_color' => '#ffffff',
            'novedades_etiqueta_color' => '#d72732',
            'novedades_titulo_color' => '#011e41',
            'novedades_descripcion_color' => '#5f6b7c',
            'telefono' => null,
            'whatsapp' => null,
            'correo_contacto' => null,
            'horario_atencion' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'direccion' => null,
            'ubicacion_url' => null,
            'directorio' => [],
            'enlaces_relacionados' => [[
                'nombre' => 'Cruz Roja Chilena',
                'url' => 'https://www.cruzroja.cl',
            ]],
        ];
    }

    private function publicSettings(): array
    {
        $settings = array_merge($this->defaultTexts(), PortadaAjuste::first()?->toArray() ?? []);
        $directory = collect($settings['directorio'] ?? []);
        $volunteers = Voluntario::with('archivoFotoPerfil')
            ->whereIn('id', $directory->pluck('voluntario_id')->filter())
            ->get()->keyBy('id');

        $settings['directorio'] = $directory->map(function ($entry) use ($volunteers) {
            $volunteer = $volunteers->get($entry['voluntario_id'] ?? null);
            if (! $volunteer) return null;
            return [
                'voluntario_id' => $volunteer->id,
                'cargo' => $entry['cargo'] ?? null,
                'nombre' => trim($volunteer->nombres.' '.$volunteer->apellidos),
                'email' => $volunteer->correo_electronico,
                'foto_url' => $volunteer->foto_perfil_url,
            ];
        })->filter()->values()->all();

        return $settings;
    }
}
