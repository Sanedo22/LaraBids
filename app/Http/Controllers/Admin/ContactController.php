<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Include soft deleted contacts
            $contacts = Contact::withTrashed()->latestFirst();

            return DataTables::of($contacts)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($contact) {
                    // If soft deleted, show deleted badge in RED
                    if ($contact->trashed()) {
                        return '<span class="badge bg-danger text-white"></i> Deleted</span>';
                    }

                    $badges = [
                        'unread' => '<span class="badge bg-warning">Unread</span>',
                        'read' => '<span class="badge bg-info">Read</span>',
                        'replied' => '<span class="badge bg-success">Replied</span>',
                    ];
                    return $badges[$contact->status] ?? '';
                })
                ->addColumn('created_at_formatted', function ($contact) {
                    return $contact->created_at->format('M d, Y h:i A');
                })
                ->addColumn('action', function ($contact) {
                    $viewBtn = '<a href="' . route('admin.contacts.show', $contact->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';

                    // If soft deleted, show restore button (Category Style)
                    if ($contact->trashed()) {
                        $restoreBtn = '<button class="btn btn-sm btn-success restore-contact" data-url="' . route('admin.contacts.restore', $contact->id) . '" title="Restore"><i class="fas fa-trash-restore"></i></button>';
                        return $viewBtn . ' ' . $restoreBtn;
                    }

                    // If not deleted, show delete button
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-contact" data-url="' . route('admin.contacts.destroy', $contact->id) . '" title="Soft Delete"><i class="fas fa-trash"></i></button>';

                    return $viewBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.contacts.index');
    }

    /**
     * Display the specified contact
     */
    public function show($id)
    {
        // Include soft deleted contacts
        $contact = Contact::withTrashed()->findOrFail($id);

        // Mark as read if unread and notify admin (only if not deleted)
        if (!$contact->trashed() && $contact->status === 'unread') {
            $contact->update(['status' => 'read']);
            session()->flash('info', 'Message status automatically updated to "Read"');
        }

        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * Update contact status and notes
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:unread,read,replied',
            'admin_notes' => 'nullable|string'
        ]);

        $contact = Contact::findOrFail($id);

        // Prepare update data
        $data = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes
        ];

        // If status is 'replied', save who replied
        if ($request->status === 'replied') {
            $data['replied_by'] = auth()->id();
        }

        $contact->update($data);

        return back()->with('success', 'Contact updated successfully!');
    }

    /**
     * Remove the specified contact from storage
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);

        // Save who deleted it
        $contact->deleted_by = auth()->id();
        $contact->save();

        // Soft delete
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully!'
        ]);
    }

    /**
     * Restore soft deleted contact
     */
    public function restore($id)
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $contact->restore();

        return response()->json([
            'success' => true,
            'message' => 'Contact restored successfully!'
        ]);
    }
}
