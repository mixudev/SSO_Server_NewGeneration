<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Services\SecurityEventQuery;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Mixudev\SecurityDefense\Services\DataAuditQueryService;

class AuditController extends Controller
{
    public function index(Request $request, SecurityEventQuery $events, DataAuditQueryService $dataAudits): View
    {
        $validated = $request->validate([
            'source' => ['nullable', 'in:events,data-audits'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:50'],
        ]);
        $source = $validated['source'] ?? 'events';

        if (! in_array($source, ['events', 'data-audits'], true)) {
            $source = 'events';
        }

        $eventPaginator = $source === 'events' ? $events->paginate($request) : null;
        $actorIds = $eventPaginator?->getCollection()
            ->pluck('actor')
            ->filter()
            ->unique()
            ->values()
            ->all() ?? [];
        $actorNames = User::query()
            ->whereKey($actorIds)
            ->get(['id', 'name', 'email'])
            ->mapWithKeys(fn (User $user): array => [(string) $user->getKey() => $user->name.' · '.$user->email])
            ->all();

        return view('pages.admin.audit.index', [
            'source' => $source,
            'events' => $eventPaginator,
            'actorNames' => $actorNames,
            'dataAudits' => $source === 'data-audits'
                ? $dataAudits->getAudits($request->only(['event', 'auditable_type', 'tampered', 'search', 'range']), (int) $request->integer('per_page', 20))
                : null,
        ]);
    }

    public function export(Request $request, SecurityEventQuery $events): Response
    {
        $rows = $events->export($request);
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['event', 'risk', 'actor', 'subject', 'occurred_at', 'metadata']);

        foreach ($rows as $event) {
            fputcsv($handle, [
                $event->event,
                $event->risk,
                $event->actor,
                $event->subject,
                $event->occurred_at?->toDateTimeString(),
                json_encode($event->safeMetadata(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="security-events.csv"',
        ]);
    }
}
