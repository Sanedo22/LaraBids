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
        $query = Contact::withTrashed()->latest();

        // Status Filter
        if ($request->has('status')) {
            $status = $request->status;
            if ($status === 'trashed') {
                $query->onlyTrashed();
            } elseif ($status !== 'all' && !empty($status)) {
                 $query->where('status', $status);
            }
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
        
        $contact->update($data);
        
        if ($request->status === 'replied') {
             // Find user by email if exists to notify
             $user = User::where('email', $contact->email)->first();
             // Or send email directly if not a user (not implemented here, following logic to notify user model)
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
}
