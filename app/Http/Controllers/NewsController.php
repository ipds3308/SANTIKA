<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    private function canManageNews(Request $request): bool
    {
        return in_array(strtolower((string) $request->user()->role), ['admin', 'cs'], true);
    }

    public function index(Request $request)
    {
        abort_unless($this->canManageNews($request), 403);

        return view('pages.news', [
            'berita' => NewsItem::latest('created_at')->latest('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageNews($request), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'image_url' => ['required', 'url', 'max:2048'],
        ]);

        DB::transaction(function () use ($data): void {
            if (NewsItem::count() >= 3) {
                NewsItem::oldest('created_at')->oldest('id')->first()?->delete();
            }

            $position = ((int) NewsItem::max('position')) + 1;

            NewsItem::create([
                'position' => $position,
                'url' => $data['url'],
                'title' => $data['title'],
                'image_url' => $data['image_url'],
            ]);
        });

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(Request $request, int $id)
    {
        abort_unless($this->canManageNews($request), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'image_url' => ['required', 'url', 'max:2048'],
        ]);

        $newsItem = NewsItem::findOrFail($id);
        $newsItem->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diperbarui.',
            'data' => $newsItem->fresh(),
        ]);
    }

    public function api()
    {
        return response()->json(NewsItem::latest('created_at')->latest('id')->get());
    }

    public function update(Request $request)
    {
        abort_unless($this->canManageNews($request), 403);

        $data = $request->validate([
            'news_url_1' => ['nullable', 'url', 'max:2048'],
            'news_url_2' => ['nullable', 'url', 'max:2048'],
            'news_url_3' => ['nullable', 'url', 'max:2048'],
            'news_title_1' => ['nullable', 'string', 'max:255'],
            'news_title_2' => ['nullable', 'string', 'max:255'],
            'news_title_3' => ['nullable', 'string', 'max:255'],
            'news_image_1' => ['nullable', 'url', 'max:2048'],
            'news_image_2' => ['nullable', 'url', 'max:2048'],
            'news_image_3' => ['nullable', 'url', 'max:2048'],
        ]);

        foreach (range(1, 3) as $position) {
            $url = trim((string) ($data['news_url_' . $position] ?? ''));
            $manualTitle = trim((string) ($data['news_title_' . $position] ?? ''));
            $manualImage = trim((string) ($data['news_image_' . $position] ?? ''));
            $existing = NewsItem::where('position', $position)->first();

            if ($url === '') {
                $existing?->delete();
                continue;
            }

            $metadata = $this->fetchMetadata($url);

            NewsItem::updateOrCreate(
                ['position' => $position],
                [
                    'url' => $url,
                    'title' => $metadata['title'] ?: ($manualTitle ?: $existing?->title ?: 'Berita BPS Kabupaten Magelang'),
                    'image_url' => $metadata['image_url'] ?: ($manualImage ?: $existing?->image_url),
                ],
            );
        }

        return redirect()->back()->with('success', 'Tautan berita berhasil disimpan dan metadata telah diperbarui.');
    }

    /**
     * @return array{title: string|null, image_url: string|null}
     */
    private function fetchMetadata(string $url): array
    {
        try {
            $response = Http::retry(2, 250)
                ->timeout(8)
                ->connectTimeout(5)
                ->withHeaders(['User-Agent' => 'SANTIKA-BPS-NewsFetcher/1.0'])
                ->get($url)
                ->throw();
        } catch (ConnectionException|RequestException) {
            return ['title' => null, 'image_url' => null];
        }

        $html = $response->body();
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);
        $title = $this->metaContent($xpath, 'og:title')
            ?: $this->textContent($xpath, '//title');
        $image = $this->metaContent($xpath, 'og:image');

        if (!$image) {
            $image = $xpath->evaluate('string((//img[@src]/@src)[1])') ?: null;
        }

        return [
            'title' => $title ? Str::limit(trim($title), 255, '') : null,
            'image_url' => $image ? $this->absoluteUrl($url, trim($image)) : null,
        ];
    }

    private function metaContent(\DOMXPath $xpath, string $property): ?string
    {
        $value = $xpath->evaluate(sprintf('string((//meta[@property="%s"]/@content | //meta[@name="%s"]/@content)[1])', $property, $property));

        return $value !== '' ? $value : null;
    }

    private function textContent(\DOMXPath $xpath, string $query): ?string
    {
        $value = trim($xpath->evaluate('string((' . $query . ')[1])'));

        return $value !== '' ? $value : null;
    }

    private function absoluteUrl(string $pageUrl, string $assetUrl): string
    {
        if (filter_var($assetUrl, FILTER_VALIDATE_URL)) {
            return $assetUrl;
        }

        $page = parse_url($pageUrl);
        if (!$page || !isset($page['scheme'], $page['host'])) {
            return $assetUrl;
        }

        if (str_starts_with($assetUrl, '//')) {
            return $page['scheme'] . ':' . $assetUrl;
        }

        $origin = $page['scheme'] . '://' . $page['host'] . (isset($page['port']) ? ':' . $page['port'] : '');

        return str_starts_with($assetUrl, '/')
            ? $origin . $assetUrl
            : $origin . '/' . ltrim($assetUrl, './');
    }
}
