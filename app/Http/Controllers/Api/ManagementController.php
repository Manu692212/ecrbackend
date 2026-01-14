<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Management;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;

class ManagementController extends Controller
{
    private const IMAGE_DIMENSIONS = [
        'small' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 600, 'height' => 600],
    ];

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
        $management = Management::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($management);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
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

        $managementData = [
            'name' => $request->name,
            'position' => $request->position,
            'designation' => $request->designation,
            'bio' => $request->bio,
            'qualifications' => $request->qualifications,
            'department' => $request->department,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $resolved = $this->resolveTargetDimensions(
                $request->image_size ?? 'medium',
                $request->image_width,
                $request->image_height
            );

            $managementData = array_merge(
                $managementData,
                $this->buildImagePayloadFromFile(
                    $image,
                    $request->image_size ?? 'medium',
                    $resolved['width'],
                    $resolved['height']
                )
            );
        }

        $management = Management::create($managementData);

        return response()->json([
            'message' => 'Management member created successfully',
            'management' => $management
        ], 201);
    }

    public function show(string $id)
    {
        $management = Management::findOrFail($id);
        return response()->json($management);
    }

    public function update(Request $request, string $id)
    {
        $management = Management::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096'
                : 'nullable|string|max:500',
            'department' => 'nullable|string|max:255',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'image_size' => 'nullable|string|in:small,medium,large',
            'image_width' => 'nullable|integer|min:50|max:2000',
            'image_height' => 'nullable|integer|min:50|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            $resolved = $this->resolveTargetDimensions(
                $request->image_size ?? $management->image_size ?? 'medium',
                $request->image_width,
                $request->image_height
            );

            $data = array_merge(
                $data,
                $this->buildImagePayloadFromFile(
                    $request->file('image'),
                    $request->image_size ?? $management->image_size ?? 'medium',
                    $resolved['width'],
                    $resolved['height'],
                    $management
                )
            );
        } elseif ($request->filled('image')) {
            $data['image_data'] = null;
            $data['image_mime'] = null;
        }

        $management->update($data);

        return response()->json([
            'message' => 'Management member updated successfully',
            'management' => $management
        ]);
    }

    public function uploadImage(Request $request, string $id)
    {
        $management = Management::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_size' => 'nullable|string|in:small,medium,large',
            'image_width' => 'nullable|integer|min:50|max:2000',
            'image_height' => 'nullable|integer|min:50|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->hasFile('image')) {
            $resolved = $this->resolveTargetDimensions(
                $request->image_size ?? $management->image_size ?? 'medium',
                $request->image_width,
                $request->image_height
            );

            $management->update(
                $this->buildImagePayloadFromFile(
                    $request->file('image'),
                    $request->image_size ?? $management->image_size ?? 'medium',
                    $resolved['width'],
                    $resolved['height'],
                    $management
                )
            );

            return response()->json([
                'message' => 'Image uploaded successfully',
                'management' => $management->fresh(),
            ]);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function destroy(string $id)
    {
        $management = Management::findOrFail($id);
        $management->delete();

        return response()->json(['message' => 'Management member deleted successfully']);
    }

    public function publicList()
    {
        try {
            $management = Management::where('is_active', true)
                ->orderBy('order', 'asc')
                ->get();

            return response()->json($management);
        } catch (\Exception $e) {
            // Table might not exist yet - return empty array
            return response()->json([]);
        }
    }

    public function download(Request $request)
    {
        $members = $this->getFilteredMembers($request);

        if ($members->isEmpty()) {
            return response()->json([
                'message' => 'No Management members found for export',
            ], 404);
        }

        $rows = $this->buildExportRows($members);

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_keys(self::EXPORT_COLUMNS));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $filename = 'management_members_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $members = $this->getFilteredMembers($request);

        if ($members->isEmpty()) {
            return response()->json([
                'message' => 'No Management members found for export',
            ], 404);
        }

        $html = $this->buildManagementPdfHtml($members);
        $filename = 'management_members_' . now()->format('Y_m_d_His') . '.pdf';

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function downloadPpt(Request $request)
    {
        $members = $this->getFilteredMembers($request);

        if ($members->isEmpty()) {
            return response()->json([
                'message' => 'No Management members found for export',
            ], 404);
        }

        $presentation = $this->buildManagementPresentation($members);
        $filename = 'management_members_' . now()->format('Y_m_d_His') . '.pptx';

        return response()->streamDownload(function () use ($presentation) {
            IOFactory::createWriter($presentation, 'PowerPoint2007')->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    private function getFilteredMembers(Request $request): Collection
    {
        $query = Management::query();

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
    private function buildExportRows(Collection $members): array
    {
        return $members->map(function (Management $member) {
            $row = [];

            foreach (self::EXPORT_COLUMNS as $attribute) {
                $value = $attribute === 'image_url'
                    ? $member->image_url
                    : data_get($member, $attribute);

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

    private function buildManagementPdfHtml(Collection $members): string
    {
        $headers = array_keys(self::EXPORT_COLUMNS);
        $rows = $this->buildExportRows($members);

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
    <title>Management Members</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        h1 { text-align: center; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #cbd5f5; padding: 6px 8px; text-align: left; }
        th { background-color: #e0f2fe; font-weight: bold; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .generated-at { text-align: right; font-size: 10px; margin-bottom: 12px; color: #475569; }
    </style>
</head>
<body>
    <h1>Management Members</h1>
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

    private function buildManagementPresentation(Collection $members): PhpPresentation
    {
        $presentation = new PhpPresentation();
        $chunks = $members->chunk(self::PPT_MEMBERS_PER_SLIDE)->values();

        if ($chunks->isEmpty()) {
            return $presentation;
        }

        $firstSlide = $presentation->getActiveSlide();
        $this->populateManagementSlide($firstSlide, $chunks->first(), 1);

        for ($i = 1; $i < $chunks->count(); $i++) {
            $slide = $presentation->createSlide();
            $this->populateManagementSlide($slide, $chunks->get($i), $i + 1);
        }

        return $presentation;
    }

    private function populateManagementSlide(Slide $slide, Collection $members, int $pageNumber): void
    {
        for ($i = $slide->getShapeCollection()->count() - 1; $i >= 0; $i--) {
            $slide->removeShapeByIndex($i);
        }

        $titleShape = $slide->createRichTextShape();
        $titleShape->setHeight(60)->setWidth(900)->setOffsetX(20)->setOffsetY(20);
        $titleShape->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $titleShape->setMarginLeft(0)->setMarginRight(0);

        $titleRun = $titleShape->createTextRun('Management Team');
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

    /**
     * @return array{width:int,height:int}
     */
    private function resolveTargetDimensions(string $imageSize, ?int $customWidth, ?int $customHeight): array
    {
        $key = array_key_exists($imageSize, self::IMAGE_DIMENSIONS) ? $imageSize : 'medium';

        return [
            'width' => $customWidth ?? self::IMAGE_DIMENSIONS[$key]['width'],
            'height' => $customHeight ?? self::IMAGE_DIMENSIONS[$key]['height'],
        ];
    }

    /**
     * @return array{
     *     image_data:string,
     *     image_mime:?string,
     *     image:?string,
     *     image_size:?string,
     *     image_width:?int,
     *     image_height:?int
     * }
     */
    private function buildImagePayloadFromFile(
        UploadedFile $image,
        ?string $imageSize,
        ?int $targetWidth,
        ?int $targetHeight,
        ?Management $existing = null
    ): array {
        $contents = file_get_contents($image->getRealPath()) ?: '';
        $dimensions = @getimagesize($image->getRealPath());

        $payload = [
            'image_data' => base64_encode($contents),
            'image_mime' => $image->getMimeType() ?: $image->getClientMimeType(),
            'image' => null,
            'image_size' => $imageSize,
            'image_width' => $targetWidth ?? ($dimensions ? (int) $dimensions[0] : null),
            'image_height' => $targetHeight ?? ($dimensions ? (int) $dimensions[1] : null),
        ];

        if ($existing) {
            $existing->setAttribute('image', null);
        }

        return $payload;
    }
}
