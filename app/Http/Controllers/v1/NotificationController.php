<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendEmailNotification(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $email = $validatedData['email'];
        $subject = $validatedData['subject'];
        $message = $validatedData['message'];

        try {
            // Send email using the mail facade
            Mail::raw($message, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return response()->json(['status'=>"00",'message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['status'=>"99",'message' => 'Failed to send notification'], 500);
        }
    }


    public function sendPhoneNotification(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|',
            'message' => 'required|string',
        ]);

        $phone = $validatedData['phone'];
        $message = $validatedData['message'];

       
        return response()->json(['status'=>"00",'message' => 'Notification sent successfully']);
       
    }
}
