<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\User;
use App\Notifications\ContactReplyNotification;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $status = $request->get('status', 'all');
            $sort   = $request->get('sort', 'latest');

            // Base query (with soft deletes)
            $contacts = Contact::withTrashed();

            // Status filter
            if ($status === 'deleted') {
                $contacts->whereNotNull('deleted_at');
            } elseif ($status !== 'all') {
                $contacts->where('status', $status)->whereNull('deleted_at');
            }

            // Date filter
            if ($request->filled('start_date')) {
                $contacts->whereDate('created_at', '>=', $request->get('start_date'));
            }

            if ($request->filled('end_date')) {
                $contacts->whereDate('created_at', '<=', $request->get('end_date'));
            }

            // Sort
            $contacts->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

            return DataTables::of($contacts)
                ->addIndexColumn()

                ->addColumn('status_badge', function ($contact) {

                    if ($contact->trashed()) {
                        return '<span class="badge badge-secondary">Deleted</span>';
                    }

                    $badges = [
                        'unread'  => '<span class="badge badge-warning">Unread</span>',
                        'read'    => '<span class="badge badge-info">Read</span>',
                        'replied' => '<span class="badge badge-success">Replied</span>',
                    ];

                    return $badges[$contact->status] ?? '';
                })

                ->addColumn('created_at_formatted', function ($contact) {
                    return $contact->created_at->format('M d, Y h:i A');
                })

                ->addColumn('action', function ($contact) {
                    $btn = '<div class="d-flex justify-content-center gap-1">';

                    $btn .= '<a href="'.route('admin.contacts.show', $contact->id).'"
                                class="btn btn-sm btn-outline-info btn-action" title="View">
                                <i class="fas fa-eye"></i>
                             </a>';

                    if ($contact->trashed()) {
                        $btn .= '<button class="btn btn-sm btn-outline-success restore-contact btn-action"
                                    data-url="'.route('admin.contacts.restore', $contact->id).'"
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                 </button>';
                    } else {
                        $btn .= '<button class="btn btn-sm btn-outline-danger delete-contact btn-action"
                                    data-url="'.route('admin.contacts.destroy', $contact->id).'"
                                    title="Move to Trash">
                                    <i class="fas fa-trash"></i>
                                 </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.contacts.index', [
            'total_contacts'   => Contact::withTrashed()->count(),
            'unread_contacts'  => Contact::where('status', 'unread')->whereNull('deleted_at')->count(),
            'read_contacts'    => Contact::where('status', 'read')->whereNull('deleted_at')->count(),
            'replied_contacts' => Contact::where('status', 'replied')->whereNull('deleted_at')->count(),
        ]);
    }

    
     //Display the specified contact
     
    public function show($id)
    {
        $contact = Contact::withTrashed()->findOrFail($id);

        if (!$contact->trashed() && $contact->status === 'unread') {
            $contact->update(['status' => 'read']);
            session()->flash('info', 'Message status automatically updated to "Read"');
        }

        return view('admin.contacts.show', compact('contact'));
    }

    
     //Update contact status and notes
     
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:unread,read,replied',
            'admin_notes' => 'nullable|string',
        ]);

        $contact = Contact::findOrFail($id);

        $data = [
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ];

        if ($request->status === 'replied') {
            $data['replied_by'] = auth()->id();
        }

        $contact->update($data);

        // Notify user on reply
        if ($request->status === 'replied') {
            $user = User::where('email', $contact->email)->first();

            if ($user) {
                $user->notify(new ContactReplyNotification($contact));
            }
        }

        return back()->with('success', 'Contact updated successfully!');
    }

    
    //Soft delete contact
     
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);

        $contact->deleted_by = auth()->id();
        $contact->save();

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully!',
        ]);
    }

    
      //Restore soft deleted contact
     
    public function restore($id)
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $contact->restore();

        return response()->json([
            'success' => true,
            'message' => 'Contact restored successfully!',
        ]);
    }
}
