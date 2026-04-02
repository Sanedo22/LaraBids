<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Resources\ContactResource;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ContactReplyNotification;
use App\Models\User;

class ContactController extends Controller
{
    // List All Contacts
    public function index(Request $request)
    {
        $query = Contact::withTrashed();

        // Status Filter
        $status = $request->get('status', 'all');
        $sort   = $request->get('sort', 'latest');

        if ($status === 'deleted') {
            $query->whereNotNull('deleted_at');
        } elseif ($status !== 'all') {
            $query->where('status', $status)->whereNull('deleted_at');
        }

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        // Search Filter
        if ($request->has('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");

                    if(is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }
        }

        // Sort
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        $contacts = $query->paginate(10);

        return ContactResource::collection($contacts)->additional([
            'status' => true,
            'message' => 'Contacts retrieved successfully'
        ]);
    }

    // Show Single Contact
    public function show($id)
    {
        $contact = Contact::withTrashed()->find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found'
            ], 404);
        }

        // Auto-mark as read if unread
        if (!$contact->trashed() && $contact->status === 'unread') {
            $contact->update(['status' => 'read']);
        }

        return response()->json([
            'status' => true,
            'data'   => new ContactResource($contact)
        ]);
    }

    // Update Contact
    public function update(Request $request, $id)
    {
        $contact = Contact::withTrashed()->find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status'      => 'required|in:unread,read,replied',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ];
        
        if ($request->status === 'replied') {
            $data['replied_by'] = auth()->id();
        }

        $contact->update($data);
        
        if ($request->status === 'replied') {
             // Find user by email if exists to notify
             $user = User::where('email', $contact->email)->first();
            
             if ($user) {
                 // Assuming ContactReplyNotification exists
                 try {
                     $user->notify(new ContactReplyNotification($contact));
                 } catch (\Exception $e) {
                     // Log error or ignore if notification fails
                 }
             }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Contact updated successfully',
            'data'    => new ContactResource($contact->fresh())
        ]);
    }

    // Soft Delete Contact
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found'
            ], 404);
        }

        $contact->deleted_by = auth()->id();
        $contact->save();

        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contact moved to trash'
        ]);
    }

    // Restore Contact
    public function restore($id)
    {
        $contact = Contact::withTrashed()->find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found'
            ], 404);
        }

        $contact->restore();

        return response()->json([
            'status' => true,
            'message' => 'Contact restored successfully',
            'data'   => new ContactResource($contact)
        ]);
    }

    // Force Delete Contact
    public function forceDelete($id)
    {
        $contact = Contact::withTrashed()->find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found'
            ], 404);
        }

        $contact->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Contact permanently deleted'
        ]);
    }

    // Bulk Actions
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:contacts,id',
            'action' => 'required|string|in:delete,restore'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $action = $request->action;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $contacts = Contact::withTrashed()->whereIn('id', $ids)->get();
            $processedCount = 0;

            foreach ($contacts as $contact) {
                if ($action === 'delete') {
                    $contact->deleted_by = auth()->id();
                    $contact->save();
                    $contact->delete();
                    $processedCount++;
                } elseif ($action === 'restore') {
                    $contact->restore();
                    $processedCount++;
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $actionMessage = match($action) {
                'delete' => 'moved to trash',
                'restore' => 'restored',
                default => 'processed'
            };

            return response()->json([
                'status' => true,
                'message' => "{$processedCount} contact(s) have been successfully {$actionMessage}."
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while performing bulk action.'
            ], 500);
        }
    }
}
