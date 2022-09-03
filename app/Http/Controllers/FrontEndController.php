<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appointment;
use App\Time;
use App\User;
use App\Booking;
use App\Prescription;
use App\Mail\AppointmentMail;

class FrontEndController extends Controller
{
    public function index(Request $request)
    {
        // Set timezone
        date_default_timezone_set('America/New_York');
        // If there is set date, find the nurses
        if (request('date')) {
            
            $formatDate = date('m-d-Y', strtotime(request('date')));            
            $nurses = Appointment::where('date', $formatDate)->get();
            // $nurses = Appointment::where('date', $formatDate)->toSql();
             
            return view('welcome', compact('nurses', 'formatDate'));
        };
        // Return all nurses avalable for today to the welcome page
        $nurses = Appointment::where('date', date('m-d-Y'))->get();
       // $nurses = Appointment::where('date', date('m-d-Y'))->toSql();
        return view('welcome', compact('nurses'));
    }

    public function show($nurseId, $date)
    {
        $appointment = Appointment::where('user_id', $nurseId)->where('date', $date)->first();
        $times = Time::where('appointment_id', $appointment->id)->where('status', 0)->get();
        $user = User::where('id', $nurseId)->first();
        $nurse_id = $nurseId;
        return view('appointment', compact('times', 'date', 'user', 'nurse_id'));
    }

    public function store(Request $request)
    {
        // Set timezone
        date_default_timezone_set('America/New_York');

        $request->validate(['time' => 'required']);
        $check = $this->checkBookingTimeInterval();
        if ($check) {
            return redirect()->back()->with('errMessage', 'You already made an appointment. Please check your email for the appointment!');
        }

        $nurseId = $request->nurseId;
        $time = $request->time;
        $appointmentId = $request->appointmentId;
        $date = $request->date;
        Booking::create([
            'user_id' => auth()->user()->id,
            'nurse_id' => $nurseId,
            'time' => $time,
            'date' => $date,
            'status' => 0
        ]);
        $nurse = User::where('id', $nurseId)->first();
        Time::where('appointment_id', $appointmentId)->where('time', $time)->update(['status' => 1]);

        // Send email notification
        $mailData = [
            'name' => auth()->user()->name,
            'time' => $time,
            'date' => $date,
            'nurseName' => $nurse->name
        ];
        try {
            \Mail::to(auth()->user()->email)->send(new AppointmentMail($mailData));
        } catch (\Exception $e) {
        }

        return redirect()->back()->with('message', 'Your appointment was booked for ' . $date . ' at ' . $time . ' with ' . $nurse->name . '.');
    }

    // check if user already make a booking.
    public function checkBookingTimeInterval()
    {
        return Booking::orderby('id', 'desc')
            ->where('user_id', auth()->user()->id)
            ->whereDate('created_at', date('y-m-d'))
            ->exists();
    }

    public function myBookings()
    {
        $appointments = Booking::latest()->where('user_id', auth()->user()->id)->get();
        return view('booking.index', compact('appointments'));
    }

    public function myPrescription()
    {
        $prescriptions = Prescription::where('user_id', auth()->user()->id)->get();
        return view('my-prescription', compact('prescriptions'));
    }
}
