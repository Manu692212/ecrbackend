<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicCouncil;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\ImageManager;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;

class AcademicCouncilController extends Controller
{
    private const EXPORT_COLUMNS = [
        'ID' => 'id',
        'Name' => 'name',
        'Position' => 'position',
        'Designation' => 'designation',
        'Department' => 'department',
        'Order' => 'order',
        'Active' => 'is_active',
        'Image URL' => 'image_url',
        'Created At' => 'created_at',
        'Updated At' => 'updated_at',
    ];

    private const PPT_MEMBERS_PER_SLIDE = 4;

    public function index()
    {
        $councils = AcademicCouncil::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($councils);
    }

    private const IMAGE_DIMENSIONS = [
        'small' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 600, 'height' => 600],
    ];

    /**
     * @return array{width:int,height:int}
     */
    private function resolveTargetDimensions(string $imageSize, ?int $customWidth, ?int $customHeight): array
    {
        $sizeKey = array_key_exists($imageSize, self::IMAGE_DIMENSIONS) ? $imageSize : 'medium';

        return [
            'width' => $customWidth ?? self::IMAGE_DIMENSIONS[$sizeKey]['width'],
            'height' => $customHeight ?? self::IMAGE_DIMENSIONS[$sizeKey]['height'],
        ];
    }

    /**
     * @return array{
     *     image:?string,
     *     image_size:string,
     *     image_width:int|null,
     *     image_height:int|null,
     *     image_data:string,
     *     image_mime:string|null
     * }
     */
    private function processImageUpload(UploadedFile $image, string $imageSize, int $targetWidth, int $targetHeight): array
    {
        $binary = file_get_contents($image->getRealPath()) ?: '';
        $dimensions = @getimagesize($image->getRealPath());
        $mime = $dimensions['mime'] ?? ($image->getMimeType() ?: $image->getClientMimeType());

        if (extension_loaded('gd')) {
            try {
                $processed = (new ImageManager(new Driver()))
                    ->read($image->getRealPath())
                    ->scaleDown($targetWidth, $targetHeight);

                $binary = (string) $processed->encode();
                $dimensions = @getimagesizefromstring($binary);
                $mime = $dimensions['mime'] ?? $mime;
            } catch (DriverException $exception) {
                Log::warning('GD driver failed to resize Academic Council image: ' . $exception->getMessage());
            } catch (\Throwable $exception) {
                Log::warning('Unexpected error while resizing Academic Council image: ' . $exception->getMessage());
            }
        } else {
            Log::warning('GD extension is not available; skipping Academic Council image resizing.');
        }

        return [
            'image' => null,
            'image_size' => $imageSize,
            'image_width' => $dimensions ? (int) $dimensions[0] : null,
            'image_height' => $dimensions ? (int) $dimensions[1] : null,
            'image_data' => base64_encode($binary),
            'image_mime' => $mime,
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_size' => 'nullable|string|in:small,medium,large',
            'image_width' => 'nullable|integer|min:50|max:2000',
            'image_height' => 'nullable|integer|min:50|max:2000',
            'department' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        $councilData = [
            'name' => $validated['name'],
            'position' => $validated['position'],
            'designation' => $validated['designation'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'qualifications' => $validated['qualifications'] ?? null,
            'department' => $validated['department'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ];

        $hasEmailColumn = Schema::hasColumn('academic_councils', 'email');
        if ($hasEmailColumn) {
            $councilData['email'] = $validated['email'] ?? $this->generatePlaceholderEmail($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageSize = $validated['image_size'] ?? 'medium';

            $dimensions = $this->resolveTargetDimensions(
                $imageSize,
                $validated['image_width'] ?? null,
                $validated['image_height'] ?? null
            );
            $imageDetails = $this->processImageUpload($image, $imageSize, $dimensions['width'], $dimensions['height']);

            $councilData = array_merge($councilData, $imageDetails);
        }

        try {
            $council = AcademicCouncil::create($councilData);
        } catch (QueryException $exception) {
            if ($hasEmailColumn && str_contains($exception->getMessage(), 'academic_councils.email')) {
                $councilData['email'] = $this->generatePlaceholderEmail($validated['name']);
                $council = AcademicCouncil::create($councilData);
            } else {
                throw $exception;
            }
        }

        return response()->json([
            'message' => 'Academic Council member created successfully',
            'council' => $council
        ], 201);
    }

    public function show(string $id)
    {
        $council = AcademicCouncil::findOrFail($id);
        return response()->json($council);
    }

    public function update(Request $request, string $id)
    {
        $council = AcademicCouncil::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'image_size' => 'nullable|string|in:small,medium,large',
            'image_width' => 'nullable|integer|min:50|max:2000',
            'image_height' => 'nullable|integer|min:50|max:2000',
            'department' => 'nullable|string|max:255',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $council->update($validator->validated());

        return response()->json([
            'message' => 'Academic Council member updated successfully',
            'council' => $council
        ]);
    }

    public function uploadImage(Request $request, string $id)
    {
        $council = AcademicCouncil::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_size' => 'nullable|string|in:small,medium,large',
            'image_width' => 'nullable|integer|min:50|max:2000',
            'image_height' => 'nullable|integer|min:50|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $validated = $validator->validated();
            $imageSize = $validated['image_size'] ?? 'medium';

            $dimensions = $this->resolveTargetDimensions(
                $imageSize,
                $validated['image_width'] ?? null,
                $validated['image_height'] ?? null
            );

            $imageDetails = $this->processImageUpload($image, $imageSize, $dimensions['width'], $dimensions['height']);

            $council->update($imageDetails);

            $council->refresh();

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image_url' => $council->image_url,
            ]);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function destroy(string $id)
    {
        $council = AcademicCouncil::findOrFail($id);
        $council->delete();

        return response()->json(['message' => 'Academic Council member deleted successfully']);
    }

    public function publicList()
    {
        try {
            $councils = AcademicCouncil::where('is_active', true)
                ->orderBy('order', 'asc')
                ->get();

            return response()->json($councils);
        } catch (\Exception $e) {
            // Table might not exist yet - return empty array
            return response()->json([]);
        }
    }

    public function download(Request $request)
    {
        $councils = $this->getFilteredCouncils($request);

        if ($councils->isEmpty()) {
            return response()->json([
                'message' => 'No Academic Council members found for export',
            ], 404);
        }

        $rows = $this->buildExportRows($councils);

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_keys(self::EXPORT_COLUMNS));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $filename = 'academic_council_members_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $councils = $this->getFilteredCouncils($request);

        if ($councils->isEmpty()) {
            return response()->json([
                'message' => 'No Academic Council members found for export',
            ], 404);
        }

        $html = $this->buildCouncilPdfHtml($councils);
        $filename = 'academic_council_members_' . now()->format('Y_m_d_His') . '.pdf';

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function downloadPpt(Request $request)
    {
        $councils = $this->getFilteredCouncils($request);

        if ($councils->isEmpty()) {
            return response()->json([
                'message' => 'No Academic Council members found for export',
            ], 404);
        }

        $presentation = $this->buildCouncilPresentation($councils);
        $filename = 'academic_council_members_' . now()->format('Y_m_d_His') . '.pptx';

        return response()->streamDownload(function () use ($presentation) {
            IOFactory::createWriter($presentation, 'PowerPoint2007')->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    private function getFilteredCouncils(Request $request): Collection
    {
        $query = AcademicCouncil::query();

        if ($request->boolean('only_active')) {
            $query->where('is_active', true);
        }

        return $query->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function buildExportRows(Collection $councils): array
    {
        return $councils->map(function (AcademicCouncil $council) {
            $row = [];

            foreach (self::EXPORT_COLUMNS as $attribute) {
                $value = $attribute === 'image_url'
                    ? $council->image_url
                    : data_get($council, $attribute);

                $row[] = $this->normalizeExportValue($value);
            }

            return $row;
        })->values()->all();
    }

    private function normalizeExportValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function escapeHtmlValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    private function buildCouncilPdfHtml(Collection $councils): string
    {
        $headers = array_keys(self::EXPORT_COLUMNS);
        $rows = $this->buildExportRows($councils);

        $headerCells = implode('', array_map(function (string $header) {
            return '<th>' . $this->escapeHtmlValue($header) . '</th>';
        }, $headers));

        $bodyRows = implode('', array_map(function (array $row) {
            $cells = implode('', array_map(function (string $value) {
                return '<td>' . $this->escapeHtmlValue($value === '' ? '—' : $value) . '</td>';
            }, $row));

            return '<tr>' . $cells . '</tr>';
        }, $rows));

        $generatedAt = $this->escapeHtmlValue(now()->format('Y-m-d H:i:s'));

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Academic Council Members</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        h1 { text-align: center; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #cbd5f5; padding: 6px 8px; text-align: left; }
        th { background-color: #e0e7ff; font-weight: bold; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .generated-at { text-align: right; font-size: 10px; margin-bottom: 12px; color: #475569; }
    </style>
</head>
<body>
    <h1>Academic Council Members</h1>
    <p class="generated-at">Generated at: {$generatedAt}</p>
    <table>
        <thead>
            <tr>{$headerCells}</tr>
        </thead>
        <tbody>
            {$bodyRows}
        </tbody>
    </table>
</body>
</html>
HTML;
    }

    private function buildCouncilPresentation(Collection $councils): PhpPresentation
    {
        $presentation = new PhpPresentation();
        $chunks = $councils->chunk(self::PPT_MEMBERS_PER_SLIDE)->values();

        if ($chunks->isEmpty()) {
            return $presentation;
        }

        $firstSlide = $presentation->getActiveSlide();
        $this->populateCouncilSlide($firstSlide, $chunks->first(), 1);

        for ($i = 1; $i < $chunks->count(); $i++) {
            $slide = $presentation->createSlide();
            $this->populateCouncilSlide($slide, $chunks->get($i), $i + 1);
        }

        return $presentation;
    }

    private function populateCouncilSlide(Slide $slide, Collection $members, int $pageNumber): void
    {
        for ($i = $slide->getShapeCollection()->count() - 1; $i >= 0; $i--) {
            $slide->removeShapeByIndex($i);
        }

        $titleShape = $slide->createRichTextShape();
        $titleShape->setHeight(60)->setWidth(900)->setOffsetX(20)->setOffsetY(20);
        $titleShape->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $titleShape->setMarginLeft(0)->setMarginRight(0);

        $titleRun = $titleShape->createTextRun('Academic Council Members');
        $titleRun->getFont()->setBold(true)->setSize(28)->setColor(new Color('FF0F172A'));

        $pageParagraph = $titleShape->createParagraph();
        $pageParagraph->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $pageRun = $pageParagraph->createTextRun('Page ' . $pageNumber);
        $pageRun->getFont()->setSize(12)->setColor(new Color('FF475569'));

        $contentShape = $slide->createRichTextShape();
        $contentShape->setHeight(420)->setWidth(900)->setOffsetX(20)->setOffsetY(100);
        $contentShape->setMarginLeft(10)->setMarginTop(10);
        $contentShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $firstParagraph = true;

        foreach ($members as $member) {
            if (!$firstParagraph) {
                $contentShape->createParagraph()->createTextRun('');
            }

            $firstParagraph = false;

            $headingParagraph = $contentShape->createParagraph();
            $headingParagraph->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $headingRun = $headingParagraph->createTextRun(trim($member->name . ' — ' . $member->position));
            $headingRun->getFont()->setBold(true)->setSize(20)->setColor(new Color('FF0F172A'));

            $details = [
                'Designation' => $member->designation ?? '—',
                'Department' => $member->department ?? '—',
                'Active' => $member->is_active ? 'Yes' : 'No',
                'Updated' => $member->updated_at?->format('Y-m-d'),
            ];

            foreach ($details as $label => $value) {
                $valueText = $value ?: '—';
                $detailParagraph = $contentShape->createParagraph();
                $detailParagraph->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $detailRun = $detailParagraph->createTextRun($label . ': ' . $valueText);
                $detailRun->getFont()->setSize(15)->setColor(new Color('FF334155'));
            }
        }
    }

    private function generatePlaceholderEmail(string $name): string
    {
        $slug = Str::slug($name);

        if (blank($slug)) {
            $slug = 'member';
        }

        return sprintf('%s-%s@placeholder.local', $slug, Str::lower(Str::random(10)));
    }
}
