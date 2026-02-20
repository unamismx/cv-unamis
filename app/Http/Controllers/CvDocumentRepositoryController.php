<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvSupportDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CvDocumentRepositoryController extends Controller
{
    public function index(): View
    {
        $docs = CvSupportDocument::query()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        $publishedCvs = Cv::query()
            ->with(['user:id,name,email', 'localizations:id,cv_id,locale,title_name'])
            ->where('status', 'published')
            ->get()
            ->keyBy('user_id');

        $userIds = collect($publishedCvs->keys())
            ->merge($docs->pluck('user_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        $docsByUser = $docs->groupBy('user_id');

        $entries = $userIds
            ->map(function (int $userId) use ($users, $publishedCvs, $docsByUser) {
                $user = $users->get($userId);
                if (! $user) {
                    return null;
                }

                return [
                    'user' => $user,
                    'cv' => $publishedCvs->get($userId),
                    'documents' => ($docsByUser->get($userId) ?? collect())->groupBy('category'),
                ];
            })
            ->filter()
            ->sortBy(fn (array $row) => mb_strtolower((string) $row['user']->name))
            ->values();

        return view('cvs.documents', [
            'entries' => $entries,
            'categoryLabels' => CvSupportDocument::categoryLabels(),
            'maxFileKb' => 1024,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categories = array_keys(CvSupportDocument::categoryLabels());
        $data = $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', $categories)],
            'title' => ['nullable', 'string', 'max:180'],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'files.*.max' => 'Cada archivo debe pesar máximo 1 MB.',
            'files.*.mimes' => 'Solo se permiten archivos PDF o imagen (JPG, PNG, WEBP).',
        ]);

        $userId = (int) auth()->id();
        $customTitle = trim((string) ($data['title'] ?? ''));

        foreach (($data['files'] ?? []) as $file) {
            $baseTitle = $customTitle !== '' ? $customTitle : pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
            $title = mb_substr(trim($baseTitle), 0, 180) ?: 'Documento';
            $path = $file->store('private/cv-support-documents/user_' . $userId, 'local');

            CvSupportDocument::create([
                'user_id' => $userId,
                'category' => $data['category'],
                'title' => $title,
                'original_name' => mb_substr((string) $file->getClientOriginalName(), 0, 255),
                'file_path' => $path,
                'file_size_bytes' => (int) $file->getSize(),
                'mime_type' => mb_substr((string) $file->getClientMimeType(), 0, 120),
            ]);
        }

        return redirect('/cvs/documents')->with('ok', 'Documento(s) subido(s) correctamente.');
    }

    public function download(CvSupportDocument $document)
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(CvSupportDocument $document): RedirectResponse
    {
        if ((int) $document->user_id !== (int) auth()->id()) {
            abort(403);
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect('/cvs/documents')->with('ok', 'Documento eliminado.');
    }
}
