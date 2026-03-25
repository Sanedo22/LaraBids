<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function showForm()
    {
        $user = Auth::user();
        
        // Block if KYC is already pending
        if ($user->kyc && $user->kyc->status === 'pending') {
            return redirect()->route('user.profile')->with('info', 'Your KYC request is already being processed.');
        }

        return view('website.user.kyc_form');
    }

    // Submit KYC
    public function submitKyc(Request $request)
    {
        $user = Auth::user();
        $existingKyc = $user->kyc;
        
        // Block if KYC is already pending
        if ($existingKyc && $existingKyc->status === 'pending') {
            return redirect()->back()->with('error', 'Your KYC request is already pending verification.');
        }

        // Logic for ID number length validation
        $idRules = [
            'aadhaar' => 12,
            'pan' => 10,
            'passport' => 8,
            'driving_license' => 15
        ];
        $expectedLength = $idRules[$request->id_type] ?? null;

        $request->validate([
            'full_name' => 'required|string|max:255|min:3',
            'date_of_birth' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:male,female,other',
            'id_type' => 'required|in:aadhaar,pan,passport,driving_license',
            'id_number' => [
                'required',
                'string',
                $expectedLength ? "size:$expectedLength" : "min:5"
            ],
            'id_document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'selfie_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'signature_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'legal_declaration' => 'accepted',
        ], [
            // Personal Info
            'full_name.required' => 'Please enter your full name as it appears on your ID.',
            'full_name.min' => 'Please enter a valid full name (at least 3 characters).',
            'date_of_birth.required' => 'Your date of birth is required for verification.',
            'date_of_birth.before' => 'You must be at least 18 years old to use LaraBids.',
            'gender.required' => 'Please select your gender.',
            'legal_declaration.accepted' => 'You must agree to the legal declaration to proceed.',
            
            // ID Document
            'id_type.required' => 'Please select a type of ID document to upload.',
            'id_number.required' => 'The ID number of your selected document is required.',
            'id_number.size' => "A valid $request->id_type number must be exactly $expectedLength characters.",
            
            // Files
            'id_document.required' => 'Please upload a clear copy of your identity document.',
            'id_document.mimes' => 'Identity document must be a clear scan in JPEG, PNG, or PDF format.',
            'id_document.max' => 'Document file size is too large. Maximum limit is 5MB.',
            
            'selfie_image.required' => 'Please upload a clear selfie of yourself holding your ID.',
            'selfie_image.file' => 'The selfie file must be a valid image (JPEG/PNG) or PDF.',
            'selfie_image.mimes' => 'The selfie must be in JPEG, PNG, or PDF format.',
            'selfie_image.max' => 'Selfie file size is too large. Maximum limit is 5MB.',

            'signature_image.required' => 'Please upload a clear image of your signature.',
            'signature_image.file' => 'The signature file must be a valid image (JPEG/PNG) or PDF.',
            'signature_image.mimes' => 'The signature must be in JPEG, PNG, or PDF format.',
            'signature_image.max' => 'Signature file size is too large. Maximum limit is 5MB.',
        ]);

        $idDocumentPath = $request->file('id_document')->store('kyc/documents', 'public');
        $selfieImagePath = $request->file('selfie_image')->store('kyc/selfies', 'public');
        $signatureImagePath = $request->file('signature_image')->store('kyc/signatures', 'public');

        if ($existingKyc) {
            $isResubmitted = $existingKyc->status === 'approved';

            // Delete old files
            if ($existingKyc->id_document) {
                Storage::disk('public')->delete($existingKyc->id_document);
            }
            if ($existingKyc->selfie_image) {
                Storage::disk('public')->delete($existingKyc->selfie_image);
            }
            if ($existingKyc->signature_image) {
                Storage::disk('public')->delete($existingKyc->signature_image);
            }

            // Update existing record
            $existingKyc->update([
                'full_name' => $request->full_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'id_document' => $idDocumentPath,
                'selfie_image' => $selfieImagePath,
                'signature_image' => $signatureImagePath,
                'status' => 'pending',
                'is_resubmitted' => $isResubmitted,
                'admin_note' => null, // Clear previous rejection reason
            ]);
        } else {
            // Create new record
            Kyc::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'id_document' => $idDocumentPath,
                'selfie_image' => $selfieImagePath,
                'signature_image' => $signatureImagePath,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('user.profile')->with('success', 'KYC submitted successfully and is pending verification.');
    }

}
