<?php

namespace App\Http\Controllers;

use App\Models\SolicitudHojaVida;
use App\Services\LifeSheetApprovalService;
use App\Services\NotificationCampaignService;
use Illuminate\Http\Request;

class SolicitudHojaVidaController extends Controller
{
    public function __construct(
        private readonly LifeSheetApprovalService $approvals,
        private readonly NotificationCampaignService $notificationCampaigns
    ) {}

    public function index(Request $request)
    {
        $actor = $this->reviewer($request);
        $query = SolicitudHojaVida::with(['voluntario.filial', 'solicitante.voluntario', 'revisor.voluntario', 'archivos'])
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $filialId = $actor->voluntario?->filial_id;
        if ($filialId) {
            $query->whereHas('voluntario', fn ($q) => $q->where('filial_id', $filialId));
        }

        return response()->json($query->get());
    }

    public function mine(Request $request)
    {
        $volunteerId = $request->user()?->voluntario?->id;
        abort_unless($volunteerId, 403);

        return response()->json(
            SolicitudHojaVida::with(['archivos', 'revisor.voluntario'])
                ->where('voluntario_id', $volunteerId)->latest()->get()
        );
    }

    public function review(Request $request, SolicitudHojaVida $solicitudHojaVida)
    {
        $actor = $this->reviewer($request);
        $this->ensureSameFilial($actor, $solicitudHojaVida);
        $data = $request->validate([
            'decision' => ['required', 'in:aprobar,rechazar'],
            'motivo' => ['nullable', 'string', 'max:1500', 'required_if:decision,rechazar'],
        ]);

        $reviewed = $data['decision'] === 'aprobar'
            ? $this->approvals->approve($solicitudHojaVida, $actor, $data['motivo'] ?? null)
            : $this->approvals->reject($solicitudHojaVida, $actor, $data['motivo']);

        $this->notificationCampaigns->notifyLifeSheetDecision($reviewed, $actor);
        return response()->json($reviewed);
    }

    private function reviewer(Request $request)
    {
        $actor = $request->user()?->loadMissing('roles', 'voluntario');
        abort_unless($actor?->roles->contains(fn ($role) => in_array($role->clave, ['administrador', 'moderador'], true)), 403);
        return $actor;
    }

    private function ensureSameFilial($actor, SolicitudHojaVida $approval): void
    {
        $approval->loadMissing('voluntario');
        if ($actor->voluntario?->filial_id) {
            abort_unless((int) $actor->voluntario->filial_id === (int) $approval->voluntario?->filial_id, 403);
        }
    }
}
