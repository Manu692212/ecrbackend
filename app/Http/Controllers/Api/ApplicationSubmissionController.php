<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationSubmittedMail;
use App\Models\ApplicationSubmission;
use App\Services\SmtpConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApplicationSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = ApplicationSubmission::query()
            ->orderByDesc('created_at');

        if ($request->filled('form_type')) {
            $query->where('form_type', $request->form_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($inner) use ($search) {
                $inner->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function show(string $id)
    {
        $submission = ApplicationSubmission::findOrFail($id);

        if (!$submission->admin_viewed_at) {
            $submission->update(['admin_viewed_at' => now()]);
        }

        return response()->json($submission);
    }

    public function update(Request $request, string $id)
    {
        $submission = ApplicationSubmission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|string|in:new,in_review,contacted,closed',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $submission->update($request->only(['status', 'admin_notes']));

        return response()->json([
            'message' => 'Application updated successfully',
            'application' => $submission,
        ]);
    }

    public function destroy(string $id)
    {
        $submission = ApplicationSubmission::findOrFail($id);
        $submission->delete();

        return response()->json(['message' => 'Application removed']);
    }

    public function publicStore(Request $request, SmtpConfigService $smtpConfigService)
    {
        $validator = Validator::make($request->all(), [
            'form_type' => 'required|string|max:50',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'title' => 'nullable|string|max:255',
            'payload' => 'array',
            'message' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = $request->payload ?? [];
        if ($request->filled('message')) {
            $payload['message'] = $request->message;
        }

        $submission = ApplicationSubmission::create([
            'form_type' => $request->form_type,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'title' => $request->title,
            'status' => 'new',
            'payload' => $payload,
        ]);

        $recipientEmail = $smtpConfigService->getRecipientEmail();
        $recipientName = $smtpConfigService->getRecipientName();

        if ($recipientEmail) {
            Mail::to([$recipientEmail => $recipientName])
                ->send(new ApplicationSubmittedMail($submission));
        }

        return response()->json([
            'message' => 'Application submitted successfully',
            'submission_id' => $submission->id,
        ], 201);
    }
}
